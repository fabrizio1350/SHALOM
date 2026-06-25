--
-- PostgreSQL database dump
--

\restrict 2Q7SLEpVwEtuA65CTwl4ODuSYifBSYD1AUK9Cc6qP61Kuad5Iip72Ubrhv4doQ5

-- Dumped from database version 15.18 (Debian 15.18-0+deb12u1)
-- Dumped by pg_dump version 15.18 (Debian 15.18-0+deb12u1)

-- Started on 2026-06-24 19:00:04 -05

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 259 (class 1255 OID 17973)
-- Name: actualizar_arbol_almacen(); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.actualizar_arbol_almacen()
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_zonas CURSOR FOR 
                  SELECT * FROM zonas WHERE estado != 'eliminada';
    cur_encs  CURSOR(p_id_zona INTEGER) FOR 
                  SELECT * FROM encomiendas 
                  WHERE id_zona = p_id_zona AND estado != 'despachado';
    fila_zona zonas%ROWTYPE;
    fila_enc  encomiendas%ROWTYPE;
    id_raiz   INTEGER;
    id_zona_nodo INTEGER;
BEGIN
    -- Limpiar árbol anterior
    DELETE FROM arbol_almacen;

    -- Insertar raíz
    INSERT INTO arbol_almacen (nivel, tipo, id_nodo, nombre, estado, id_padre, created_at, updated_at)
    VALUES (0, 'almacen', '0', 'Almacén Shalom', 'activo', NULL, NOW(), NOW())
    RETURNING id INTO id_raiz;

    -- Cursor de zonas
    OPEN cur_zonas;
    LOOP
        FETCH cur_zonas INTO fila_zona;
        EXIT WHEN NOT FOUND;

        -- Insertar zona como hijo de raíz
        INSERT INTO arbol_almacen (nivel, tipo, id_nodo, nombre, estado, id_padre, created_at, updated_at)
        VALUES (1, 'zona', fila_zona.id::VARCHAR, fila_zona.nombre, fila_zona.estado, id_raiz, NOW(), NOW())
        RETURNING id INTO id_zona_nodo;

        -- Cursor de encomiendas por zona
        OPEN cur_encs(fila_zona.id);
        LOOP
            FETCH cur_encs INTO fila_enc;
            EXIT WHEN NOT FOUND;

            -- Insertar encomienda como hijo de zona
            INSERT INTO arbol_almacen (nivel, tipo, id_nodo, nombre, estado, id_padre, created_at, updated_at)
            VALUES (2, 'encomienda', fila_enc.id_encomienda,
                    fila_enc.remitente || ' → ' || fila_enc.destinatario,
                    fila_enc.estado, id_zona_nodo, NOW(), NOW());
        END LOOP;
        CLOSE cur_encs;

    END LOOP;
    CLOSE cur_zonas;

EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error actualizando árbol: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.actualizar_arbol_almacen() OWNER TO fabrizio;

--
-- TOC entry 262 (class 1255 OID 18191)
-- Name: actualizar_bst_encomiendas(); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.actualizar_bst_encomiendas()
    LANGUAGE plpgsql
    AS $$
DECLARE
    ids           VARCHAR(20)[];
    nombres       VARCHAR(100)[];
    total         INTEGER;
    id_raiz       INTEGER := NULL;
    id_nodo_nuevo INTEGER;
    id_actual     INTEGER;
    codigo_actual VARCHAR(20);
    izq           INTEGER;
    der           INTEGER;
    orden         INTEGER[];
    i             INTEGER;
    enc_id        VARCHAR(20);
    enc_nombre    VARCHAR(100);
BEGIN
    UPDATE bst_encomiendas SET id_izquierdo = NULL, id_derecho = NULL;
    DELETE FROM bst_encomiendas;

    -- Obtener IDs y nombres ordenados
    SELECT 
        ARRAY_AGG(id_encomienda ORDER BY id_encomienda ASC),
        ARRAY_AGG(remitente ORDER BY id_encomienda ASC)
    INTO ids, nombres
    FROM encomiendas WHERE estado != 'despachado';

    IF ids IS NULL THEN RETURN; END IF;
    total := array_length(ids, 1);

    -- Construir orden balanceado: medio, luego recursivo
    orden := ARRAY[]::INTEGER[];
    orden := orden || (total/2 + 1); -- insertar el del medio primero

    -- Agregar los demás en orden de registro
    FOR i IN 1..total LOOP
        IF i != (total/2 + 1) THEN
            orden := orden || i;
        END IF;
    END LOOP;

    -- Insertar en orden balanceado
    FOR i IN 1..total LOOP
        enc_id     := ids[orden[i]];
        enc_nombre := nombres[orden[i]];

        INSERT INTO bst_encomiendas (nombre, id_encomienda, created_at, updated_at)
        VALUES (enc_nombre, enc_id, NOW(), NOW())
        RETURNING id INTO id_nodo_nuevo;

        IF id_raiz IS NULL THEN
            id_raiz := id_nodo_nuevo;
        ELSE
            id_actual := id_raiz;
            LOOP
                SELECT id_encomienda, id_izquierdo, id_derecho
                INTO codigo_actual, izq, der
                FROM bst_encomiendas WHERE id = id_actual;

                IF enc_id < codigo_actual THEN
                    IF izq IS NULL THEN
                        UPDATE bst_encomiendas SET id_izquierdo = id_nodo_nuevo WHERE id = id_actual;
                        EXIT;
                    ELSE
                        id_actual := izq;
                    END IF;
                ELSE
                    IF der IS NULL THEN
                        UPDATE bst_encomiendas SET id_derecho = id_nodo_nuevo WHERE id = id_actual;
                        EXIT;
                    ELSE
                        id_actual := der;
                    END IF;
                END IF;
            END LOOP;
        END IF;
    END LOOP;

EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error actualizando BST: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.actualizar_bst_encomiendas() OWNER TO fabrizio;

--
-- TOC entry 252 (class 1255 OID 16751)
-- Name: actualizar_estado_zona(integer); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.actualizar_estado_zona(IN p_id_zona integer)
    LANGUAGE plpgsql
    AS $$
DECLARE
    fila_zona    zonas%ROWTYPE;
    ocupacion    INTEGER;
    nuevo_estado VARCHAR(25);
BEGIN
    SELECT * INTO STRICT fila_zona FROM zonas WHERE id = p_id_zona;
    SELECT COUNT(*) INTO ocupacion 
    FROM encomiendas 
    WHERE id_zona = p_id_zona AND estado NOT IN ('despachado');
    IF ocupacion = 0 THEN
        nuevo_estado := 'disponible';
    ELSIF ocupacion < fila_zona.capacidad THEN
        nuevo_estado := 'parcialmente_ocupada';
    ELSE
        nuevo_estado := 'llena';
    END IF;
    UPDATE zonas SET estado = nuevo_estado WHERE id = p_id_zona;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE EXCEPTION 'Zona % no encontrada', p_id_zona;
END;
$$;


ALTER PROCEDURE public.actualizar_estado_zona(IN p_id_zona integer) OWNER TO fabrizio;

--
-- TOC entry 253 (class 1255 OID 16753)
-- Name: cambiar_estado_encomienda(character varying, character varying, text, bigint); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.cambiar_estado_encomienda(IN p_id_encomienda character varying, IN p_estado_nuevo character varying, IN p_observacion text, IN p_id_usuario bigint)
    LANGUAGE plpgsql
    AS $$
DECLARE
    fila_enc encomiendas%ROWTYPE;
BEGIN
    SELECT * INTO STRICT fila_enc 
    FROM encomiendas WHERE id_encomienda = p_id_encomienda;
    UPDATE encomiendas 
    SET estado = p_estado_nuevo, updated_at = NOW()
    WHERE id_encomienda = p_id_encomienda;
    INSERT INTO historial_movimientos (
        id_encomienda, estado_anterior, estado_nuevo, observacion, id_usuario, created_at
    ) VALUES (
        p_id_encomienda, fila_enc.estado, p_estado_nuevo, p_observacion, p_id_usuario, NOW()
    );
    IF p_estado_nuevo = 'despachado' AND fila_enc.id_zona IS NOT NULL THEN
        CALL actualizar_estado_zona(fila_enc.id_zona);
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE EXCEPTION 'Encomienda % no encontrada', p_id_encomienda;
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error al cambiar estado: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.cambiar_estado_encomienda(IN p_id_encomienda character varying, IN p_estado_nuevo character varying, IN p_observacion text, IN p_id_usuario bigint) OWNER TO fabrizio;

--
-- TOC entry 256 (class 1255 OID 16754)
-- Name: generar_alertas_tiempo(); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.generar_alertas_tiempo()
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_enc      CURSOR FOR 
                     SELECT * FROM encomiendas 
                     WHERE estado NOT IN ('despachado', 'tiempo_excedido');
    fila_enc     encomiendas%ROWTYPE;
    tiempo_max   INTEGER;
    dias_almacen INTEGER;
    ya_existe    INTEGER;
BEGIN
    SELECT tiempo_maximo_dias INTO tiempo_max FROM configuracion LIMIT 1;
    IF tiempo_max IS NULL THEN tiempo_max := 7; END IF;
    OPEN cur_enc;
    LOOP
        FETCH cur_enc INTO fila_enc;
        EXIT WHEN NOT FOUND;
        dias_almacen := EXTRACT(DAY FROM NOW() - fila_enc.fecha_ingreso)::INTEGER;
        IF dias_almacen > tiempo_max THEN
            SELECT COUNT(*) INTO ya_existe 
            FROM alertas 
            WHERE id_encomienda = fila_enc.id_encomienda 
              AND tipo = 'tiempo_excedido'
              AND estado IN ('generada', 'notificada', 'atendida');
            IF ya_existe = 0 THEN
                INSERT INTO alertas (id_encomienda, tipo, estado, fecha_generada, created_at, updated_at)
                VALUES (fila_enc.id_encomienda, 'tiempo_excedido', 'generada', NOW(), NOW(), NOW());
                UPDATE encomiendas 
                SET estado = 'tiempo_excedido', updated_at = NOW()
                WHERE id_encomienda = fila_enc.id_encomienda;
            END IF;
        END IF;
    END LOOP;
    CLOSE cur_enc;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error generando alertas: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.generar_alertas_tiempo() OWNER TO fabrizio;

--
-- TOC entry 254 (class 1255 OID 17078)
-- Name: limpiar_encomiendas_despachadas(); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.limpiar_encomiendas_despachadas()
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_desp CURSOR FOR
                 SELECT * FROM encomiendas
                 WHERE estado = 'despachado'
                 AND updated_at < NOW() - INTERVAL '7 days';
    fila_enc encomiendas%ROWTYPE;
BEGIN
    OPEN cur_desp;
    LOOP
        FETCH cur_desp INTO fila_enc;
        EXIT WHEN NOT FOUND;
        DELETE FROM historial_movimientos 
        WHERE id_encomienda = fila_enc.id_encomienda;
        DELETE FROM alertas 
        WHERE id_encomienda = fila_enc.id_encomienda;
        DELETE FROM encomiendas 
        WHERE id_encomienda = fila_enc.id_encomienda;
    END LOOP;
    CLOSE cur_desp;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error en limpieza: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.limpiar_encomiendas_despachadas() OWNER TO fabrizio;

--
-- TOC entry 257 (class 1255 OID 17959)
-- Name: obtener_alertas_activas(); Type: FUNCTION; Schema: public; Owner: fabrizio
--

CREATE FUNCTION public.obtener_alertas_activas() RETURNS TABLE(id integer, id_encomienda character varying, tipo character varying, estado character varying, fecha_generada timestamp without time zone)
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_alertas CURSOR FOR
                    SELECT a.id, a.id_encomienda, a.tipo, a.estado, a.fecha_generada
                    FROM alertas a
                    WHERE a.estado != 'resuelta'
                    ORDER BY a.fecha_generada ASC;
    fila RECORD;
BEGIN
    OPEN cur_alertas;
    LOOP
        FETCH cur_alertas INTO fila;
        EXIT WHEN NOT FOUND;
        id             := fila.id;
        id_encomienda  := fila.id_encomienda;
        tipo           := fila.tipo;
        estado         := fila.estado;
        fecha_generada := fila.fecha_generada;
        RETURN NEXT;
    END LOOP;
    CLOSE cur_alertas;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error obteniendo alertas: %', SQLERRM;
END;
$$;


ALTER FUNCTION public.obtener_alertas_activas() OWNER TO fabrizio;

--
-- TOC entry 258 (class 1255 OID 17960)
-- Name: obtener_arbol_almacen(); Type: FUNCTION; Schema: public; Owner: fabrizio
--

CREATE FUNCTION public.obtener_arbol_almacen() RETURNS TABLE(nivel integer, tipo character varying, id_nodo character varying, nombre character varying, estado character varying, id_padre character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    WITH RECURSIVE arbol_almacen AS (
        -- Nivel 0: Raíz del almacén
        SELECT 
            0 AS nivel,
            'almacen'::VARCHAR AS tipo,
            '0'::VARCHAR AS id_nodo,
            'Almacén Shalom'::VARCHAR AS nombre,
            'activo'::VARCHAR AS estado,
            NULL::VARCHAR AS id_padre

        UNION ALL

        -- Nivel 1: Zonas
        SELECT 
            1,
            'zona'::VARCHAR,
            z.id::VARCHAR,
            z.nombre,
            z.estado,
            '0'::VARCHAR
        FROM zonas z
        WHERE z.estado != 'eliminada'

        UNION ALL

        -- Nivel 2: Encomiendas dentro de cada zona
        SELECT 
            2,
            'encomienda'::VARCHAR,
            e.id_encomienda::VARCHAR,
            e.remitente || ' → ' || e.destinatario,
            e.estado,
            e.id_zona::VARCHAR
        FROM encomiendas e
        WHERE e.estado != 'despachado'
    )
    SELECT * FROM arbol_almacen
    ORDER BY nivel, id_padre, id_nodo;
END;
$$;


ALTER FUNCTION public.obtener_arbol_almacen() OWNER TO fabrizio;

--
-- TOC entry 255 (class 1255 OID 17120)
-- Name: obtener_historial_encomienda(character varying); Type: FUNCTION; Schema: public; Owner: fabrizio
--

CREATE FUNCTION public.obtener_historial_encomienda(p_id_encomienda character varying) RETURNS TABLE(estado_anterior character varying, estado_nuevo character varying, observacion text, usuario character varying, fecha timestamp without time zone)
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_hist CURSOR FOR
                 SELECT hm.estado_anterior, hm.estado_nuevo, hm.observacion,
                        u.name, hm.created_at
                 FROM historial_movimientos hm
                 JOIN users u ON u.id = hm.id_usuario
                 WHERE hm.id_encomienda = p_id_encomienda
                 ORDER BY hm.created_at DESC;
    fila RECORD;
BEGIN
    OPEN cur_hist;
    LOOP
        FETCH cur_hist INTO fila;
        EXIT WHEN NOT FOUND;
        estado_anterior := fila.estado_anterior;
        estado_nuevo    := fila.estado_nuevo;
        observacion     := fila.observacion;
        usuario         := fila.name;
        fecha           := fila.created_at;
        RETURN NEXT;
    END LOOP;
    CLOSE cur_hist;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error obteniendo historial: %', SQLERRM;
END;
$$;


ALTER FUNCTION public.obtener_historial_encomienda(p_id_encomienda character varying) OWNER TO fabrizio;

--
-- TOC entry 251 (class 1255 OID 16752)
-- Name: registrar_encomienda(character varying, character varying, character varying, numeric, character varying, text, bigint); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.registrar_encomienda(IN p_remitente character varying, IN p_destinatario character varying, IN p_ciudad_destino character varying, IN p_peso numeric, IN p_dimensiones character varying, IN p_descripcion text, IN p_id_usuario bigint)
    LANGUAGE plpgsql
    AS $$
DECLARE
    cur_zonas     CURSOR FOR 
                      SELECT * FROM zonas 
                      WHERE estado != 'llena' AND estado != 'eliminada'
                      ORDER BY id;
    fila_zona     zonas%ROWTYPE;
    nuevo_codigo  VARCHAR(20);
    zona_asignada INTEGER := NULL;
    contador      INTEGER;
BEGIN
    SELECT COUNT(*) INTO contador
    FROM encomiendas
    WHERE fecha_ingreso >= DATE_TRUNC('month', NOW());
    nuevo_codigo := 'SHL-' || TO_CHAR(NOW(), 'YYYY-MM-') || 
                   LPAD((contador + 1)::TEXT, 6, '0');
    OPEN cur_zonas;
    LOOP
        FETCH cur_zonas INTO fila_zona;
        EXIT WHEN NOT FOUND;
        IF zona_asignada IS NULL THEN
            zona_asignada := fila_zona.id;
            EXIT;
        END IF;
    END LOOP;
    CLOSE cur_zonas;
    INSERT INTO encomiendas (
        id_encomienda, remitente, destinatario, ciudad_destino,
        peso, dimensiones, descripcion, estado, id_zona, fecha_ingreso,
        created_at, updated_at
    ) VALUES (
        nuevo_codigo, p_remitente, p_destinatario, p_ciudad_destino,
        p_peso, p_dimensiones, p_descripcion, 'clasificado', zona_asignada, NOW(),
        NOW(), NOW()
    );
    INSERT INTO historial_movimientos (
        id_encomienda, estado_anterior, estado_nuevo, observacion, id_usuario, created_at
    ) VALUES (
        nuevo_codigo, 'recibido', 'clasificado', 
        'Registro y clasificacion automatica', p_id_usuario, NOW()
    );
    CALL actualizar_estado_zona(zona_asignada);
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error al registrar encomienda: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.registrar_encomienda(IN p_remitente character varying, IN p_destinatario character varying, IN p_ciudad_destino character varying, IN p_peso numeric, IN p_dimensiones character varying, IN p_descripcion text, IN p_id_usuario bigint) OWNER TO fabrizio;

--
-- TOC entry 263 (class 1255 OID 17079)
-- Name: reubicar_encomienda(character varying, text, bigint); Type: PROCEDURE; Schema: public; Owner: fabrizio
--

CREATE PROCEDURE public.reubicar_encomienda(IN p_id_encomienda character varying, IN p_observacion text, IN p_id_usuario bigint)
    LANGUAGE plpgsql
    AS $$
DECLARE
    fila_enc     encomiendas%ROWTYPE;
    zona_reubic  INTEGER;
BEGIN
    SELECT id_zona_reubicacion INTO zona_reubic 
    FROM configuracion LIMIT 1;
    SELECT * INTO STRICT fila_enc 
    FROM encomiendas WHERE id_encomienda = p_id_encomienda;
    IF zona_reubic IS NULL THEN
        zona_reubic := fila_enc.id_zona;
    END IF;
    UPDATE encomiendas 
    SET estado = 'en_espera', 
        id_zona = zona_reubic,
        updated_at = NOW()
    WHERE id_encomienda = p_id_encomienda;
    -- Usa el estado real de la encomienda en lugar de hardcodear 'tiempo_excedido'
    INSERT INTO historial_movimientos (
        id_encomienda, estado_anterior, estado_nuevo, observacion, id_usuario, created_at
    ) VALUES (
        p_id_encomienda, fila_enc.estado, 'en_espera',
        p_observacion, p_id_usuario, NOW()
    );
    CALL actualizar_estado_zona(fila_enc.id_zona);
    IF zona_reubic != fila_enc.id_zona THEN
        CALL actualizar_estado_zona(zona_reubic);
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE EXCEPTION 'Encomienda % no encontrada', p_id_encomienda;
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error al reubicar: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.reubicar_encomienda(IN p_id_encomienda character varying, IN p_observacion text, IN p_id_usuario bigint) OWNER TO fabrizio;

--
-- TOC entry 260 (class 1255 OID 17974)
-- Name: trigger_actualizar_arbol(); Type: FUNCTION; Schema: public; Owner: fabrizio
--

CREATE FUNCTION public.trigger_actualizar_arbol() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    CALL actualizar_arbol_almacen();
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trigger_actualizar_arbol() OWNER TO fabrizio;

--
-- TOC entry 261 (class 1255 OID 18192)
-- Name: trigger_actualizar_bst(); Type: FUNCTION; Schema: public; Owner: fabrizio
--

CREATE FUNCTION public.trigger_actualizar_bst() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    CALL actualizar_bst_encomiendas();
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trigger_actualizar_bst() OWNER TO fabrizio;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 231 (class 1259 OID 18095)
-- Name: alertas; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.alertas (
    id integer NOT NULL,
    id_encomienda character varying(20) NOT NULL,
    tipo character varying(50) NOT NULL,
    estado character varying(20) DEFAULT 'generada'::character varying NOT NULL,
    fecha_generada timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.alertas OWNER TO fabrizio;

--
-- TOC entry 230 (class 1259 OID 18094)
-- Name: alertas_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.alertas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.alertas_id_seq OWNER TO fabrizio;

--
-- TOC entry 3532 (class 0 OID 0)
-- Dependencies: 230
-- Name: alertas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.alertas_id_seq OWNED BY public.alertas.id;


--
-- TOC entry 237 (class 1259 OID 18155)
-- Name: arbol_almacen; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.arbol_almacen (
    id integer NOT NULL,
    nivel integer NOT NULL,
    tipo character varying(20) NOT NULL,
    id_nodo character varying(20) NOT NULL,
    nombre character varying(200) NOT NULL,
    estado character varying(25) NOT NULL,
    id_padre integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.arbol_almacen OWNER TO fabrizio;

--
-- TOC entry 236 (class 1259 OID 18154)
-- Name: arbol_almacen_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.arbol_almacen_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.arbol_almacen_id_seq OWNER TO fabrizio;

--
-- TOC entry 3533 (class 0 OID 0)
-- Dependencies: 236
-- Name: arbol_almacen_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.arbol_almacen_id_seq OWNED BY public.arbol_almacen.id;


--
-- TOC entry 239 (class 1259 OID 18169)
-- Name: bst_encomiendas; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.bst_encomiendas (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    id_encomienda character varying(20) NOT NULL,
    id_izquierdo integer,
    id_derecho integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bst_encomiendas OWNER TO fabrizio;

--
-- TOC entry 238 (class 1259 OID 18168)
-- Name: bst_encomiendas_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.bst_encomiendas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bst_encomiendas_id_seq OWNER TO fabrizio;

--
-- TOC entry 3534 (class 0 OID 0)
-- Dependencies: 238
-- Name: bst_encomiendas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.bst_encomiendas_id_seq OWNED BY public.bst_encomiendas.id;


--
-- TOC entry 220 (class 1259 OID 18022)
-- Name: cache; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO fabrizio;

--
-- TOC entry 221 (class 1259 OID 18030)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO fabrizio;

--
-- TOC entry 235 (class 1259 OID 18129)
-- Name: configuracion; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.configuracion (
    id integer NOT NULL,
    tiempo_maximo_dias integer DEFAULT 7 NOT NULL,
    id_zona_reubicacion integer,
    fecha_actualizacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    id_admin bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    peso_maximo_pequeno numeric(8,2) DEFAULT '5'::numeric NOT NULL,
    peso_maximo_mediano numeric(8,2) DEFAULT '20'::numeric NOT NULL
);


ALTER TABLE public.configuracion OWNER TO fabrizio;

--
-- TOC entry 234 (class 1259 OID 18128)
-- Name: configuracion_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.configuracion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.configuracion_id_seq OWNER TO fabrizio;

--
-- TOC entry 3535 (class 0 OID 0)
-- Dependencies: 234
-- Name: configuracion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.configuracion_id_seq OWNED BY public.configuracion.id;


--
-- TOC entry 229 (class 1259 OID 18080)
-- Name: encomiendas; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.encomiendas (
    id_encomienda character varying(20) NOT NULL,
    remitente character varying(100) NOT NULL,
    destinatario character varying(100) NOT NULL,
    ciudad_destino character varying(100) NOT NULL,
    peso numeric(8,2) NOT NULL,
    dimensiones character varying(50),
    descripcion text,
    estado character varying(20) DEFAULT 'recibido'::character varying NOT NULL,
    id_zona integer,
    fecha_ingreso timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    imagen character varying(255)
);


ALTER TABLE public.encomiendas OWNER TO fabrizio;

--
-- TOC entry 226 (class 1259 OID 18056)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO fabrizio;

--
-- TOC entry 225 (class 1259 OID 18055)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO fabrizio;

--
-- TOC entry 3536 (class 0 OID 0)
-- Dependencies: 225
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 233 (class 1259 OID 18109)
-- Name: historial_movimientos; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.historial_movimientos (
    id integer NOT NULL,
    id_encomienda character varying(20) NOT NULL,
    estado_anterior character varying(20) NOT NULL,
    estado_nuevo character varying(20) NOT NULL,
    observacion text,
    id_usuario bigint NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.historial_movimientos OWNER TO fabrizio;

--
-- TOC entry 232 (class 1259 OID 18108)
-- Name: historial_movimientos_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.historial_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.historial_movimientos_id_seq OWNER TO fabrizio;

--
-- TOC entry 3537 (class 0 OID 0)
-- Dependencies: 232
-- Name: historial_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.historial_movimientos_id_seq OWNED BY public.historial_movimientos.id;


--
-- TOC entry 224 (class 1259 OID 18048)
-- Name: job_batches; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO fabrizio;

--
-- TOC entry 223 (class 1259 OID 18039)
-- Name: jobs; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO fabrizio;

--
-- TOC entry 222 (class 1259 OID 18038)
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.jobs_id_seq OWNER TO fabrizio;

--
-- TOC entry 3538 (class 0 OID 0)
-- Dependencies: 222
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 215 (class 1259 OID 17989)
-- Name: migrations; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO fabrizio;

--
-- TOC entry 214 (class 1259 OID 17988)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO fabrizio;

--
-- TOC entry 3539 (class 0 OID 0)
-- Dependencies: 214
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 218 (class 1259 OID 18006)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO fabrizio;

--
-- TOC entry 219 (class 1259 OID 18013)
-- Name: sessions; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO fabrizio;

--
-- TOC entry 217 (class 1259 OID 17996)
-- Name: users; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    rol character varying(20) DEFAULT 'operario'::character varying NOT NULL,
    estado character varying(10) DEFAULT 'activo'::character varying NOT NULL
);


ALTER TABLE public.users OWNER TO fabrizio;

--
-- TOC entry 216 (class 1259 OID 17995)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO fabrizio;

--
-- TOC entry 3540 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 228 (class 1259 OID 18070)
-- Name: zonas; Type: TABLE; Schema: public; Owner: fabrizio
--

CREATE TABLE public.zonas (
    id integer NOT NULL,
    nombre character varying(50) NOT NULL,
    capacidad integer DEFAULT 10 NOT NULL,
    estado character varying(25) DEFAULT 'disponible'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.zonas OWNER TO fabrizio;

--
-- TOC entry 227 (class 1259 OID 18069)
-- Name: zonas_id_seq; Type: SEQUENCE; Schema: public; Owner: fabrizio
--

CREATE SEQUENCE public.zonas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.zonas_id_seq OWNER TO fabrizio;

--
-- TOC entry 3541 (class 0 OID 0)
-- Dependencies: 227
-- Name: zonas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: fabrizio
--

ALTER SEQUENCE public.zonas_id_seq OWNED BY public.zonas.id;


--
-- TOC entry 3293 (class 2604 OID 18098)
-- Name: alertas id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.alertas ALTER COLUMN id SET DEFAULT nextval('public.alertas_id_seq'::regclass);


--
-- TOC entry 3303 (class 2604 OID 18158)
-- Name: arbol_almacen id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.arbol_almacen ALTER COLUMN id SET DEFAULT nextval('public.arbol_almacen_id_seq'::regclass);


--
-- TOC entry 3304 (class 2604 OID 18172)
-- Name: bst_encomiendas id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.bst_encomiendas ALTER COLUMN id SET DEFAULT nextval('public.bst_encomiendas_id_seq'::regclass);


--
-- TOC entry 3298 (class 2604 OID 18132)
-- Name: configuracion id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.configuracion ALTER COLUMN id SET DEFAULT nextval('public.configuracion_id_seq'::regclass);


--
-- TOC entry 3286 (class 2604 OID 18059)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 3296 (class 2604 OID 18112)
-- Name: historial_movimientos id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.historial_movimientos ALTER COLUMN id SET DEFAULT nextval('public.historial_movimientos_id_seq'::regclass);


--
-- TOC entry 3285 (class 2604 OID 18042)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 3281 (class 2604 OID 17992)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 3282 (class 2604 OID 17999)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3288 (class 2604 OID 18073)
-- Name: zonas id; Type: DEFAULT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.zonas ALTER COLUMN id SET DEFAULT nextval('public.zonas_id_seq'::regclass);


--
-- TOC entry 3518 (class 0 OID 18095)
-- Dependencies: 231
-- Data for Name: alertas; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.alertas (id, id_encomienda, tipo, estado, fecha_generada, created_at, updated_at) FROM stdin;
1	SHL-2026-06-000006	tiempo_excedido	resuelta	2026-06-22 09:23:21	2026-06-22 09:23:21	2026-06-22 14:24:30
2	SHL-2026-06-000007	tiempo_excedido	resuelta	2026-06-22 09:23:21	2026-06-22 09:23:21	2026-06-22 14:50:21
3	SHL-2026-06-000008	tiempo_excedido	resuelta	2026-06-22 09:23:21	2026-06-22 09:23:21	2026-06-22 15:44:40
4	SHL-2026-06-000001	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
5	SHL-2026-06-000002	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
6	SHL-2026-06-000003	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
7	SHL-2026-06-000004	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
8	SHL-2026-06-000005	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
9	SHL-2026-06-000009	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
10	SHL-2026-06-000010	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
11	SHL-2026-06-000011	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
12	SHL-2026-06-000008	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
13	SHL-2026-06-000007	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
14	SHL-2026-06-000006	tiempo_excedido	generada	2026-06-24 19:00:02	2026-06-24 19:00:02	2026-06-24 19:00:02
\.


--
-- TOC entry 3524 (class 0 OID 18155)
-- Dependencies: 237
-- Data for Name: arbol_almacen; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.arbol_almacen (id, nivel, tipo, id_nodo, nombre, estado, id_padre, created_at, updated_at) FROM stdin;
609	0	almacen	0	Almacén Shalom	activo	\N	2026-06-24 19:00:02	2026-06-24 19:00:02
610	1	zona	2	Zona B	disponible	609	2026-06-24 19:00:02	2026-06-24 19:00:02
611	1	zona	3	Zona C	parcialmente_ocupada	609	2026-06-24 19:00:02	2026-06-24 19:00:02
612	2	encomienda	SHL-2026-06-000006	Roberto Quispe → Sandra Lima	tiempo_excedido	611	2026-06-24 19:00:02	2026-06-24 19:00:02
613	1	zona	4	Zona D	disponible	609	2026-06-24 19:00:02	2026-06-24 19:00:02
614	1	zona	1	Zona A	parcialmente_ocupada	609	2026-06-24 19:00:02	2026-06-24 19:00:02
615	2	encomienda	SHL-2026-06-000001	Juan Perez → Maria Garcia	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
616	2	encomienda	SHL-2026-06-000002	Carlos Lopez → Ana Torres	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
617	2	encomienda	SHL-2026-06-000003	Luis Mamani → Rosa Quispe	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
618	2	encomienda	SHL-2026-06-000004	Pedro Flores → Carmen Huanca	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
619	2	encomienda	SHL-2026-06-000005	Jose Condori → Elena Vargas	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
620	2	encomienda	SHL-2026-06-000009	Roberto Quispe → Sandra Lima	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
621	2	encomienda	SHL-2026-06-000010	Miguel Torres → Carmen Puno	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
622	2	encomienda	SHL-2026-06-000011	Diana Flores → Jorge Cusco	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
623	2	encomienda	SHL-2026-06-000008	Diana Flores → Jorge Cusco	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
624	2	encomienda	SHL-2026-06-000007	Miguel Torres → Carmen Puno	tiempo_excedido	614	2026-06-24 19:00:02	2026-06-24 19:00:02
\.


--
-- TOC entry 3526 (class 0 OID 18169)
-- Dependencies: 239
-- Data for Name: bst_encomiendas; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.bst_encomiendas (id, nombre, id_encomienda, id_izquierdo, id_derecho, created_at, updated_at) FROM stdin;
478	Juan Perez	SHL-2026-06-000001	\N	479	2026-06-24 19:00:02	2026-06-24 19:00:02
479	Carlos Lopez	SHL-2026-06-000002	\N	480	2026-06-24 19:00:02	2026-06-24 19:00:02
480	Luis Mamani	SHL-2026-06-000003	\N	481	2026-06-24 19:00:02	2026-06-24 19:00:02
482	Jose Condori	SHL-2026-06-000005	\N	\N	2026-06-24 19:00:02	2026-06-24 19:00:02
481	Pedro Flores	SHL-2026-06-000004	\N	482	2026-06-24 19:00:02	2026-06-24 19:00:02
477	Roberto Quispe	SHL-2026-06-000006	478	483	2026-06-24 19:00:02	2026-06-24 19:00:02
483	Miguel Torres	SHL-2026-06-000007	\N	484	2026-06-24 19:00:02	2026-06-24 19:00:02
484	Diana Flores	SHL-2026-06-000008	\N	485	2026-06-24 19:00:02	2026-06-24 19:00:02
485	Roberto Quispe	SHL-2026-06-000009	\N	486	2026-06-24 19:00:02	2026-06-24 19:00:02
487	Diana Flores	SHL-2026-06-000011	\N	\N	2026-06-24 19:00:02	2026-06-24 19:00:02
486	Miguel Torres	SHL-2026-06-000010	\N	487	2026-06-24 19:00:02	2026-06-24 19:00:02
\.


--
-- TOC entry 3507 (class 0 OID 18022)
-- Dependencies: 220
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- TOC entry 3508 (class 0 OID 18030)
-- Dependencies: 221
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 3522 (class 0 OID 18129)
-- Dependencies: 235
-- Data for Name: configuracion; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.configuracion (id, tiempo_maximo_dias, id_zona_reubicacion, fecha_actualizacion, id_admin, created_at, updated_at, peso_maximo_pequeno, peso_maximo_mediano) FROM stdin;
1	1	3	2026-06-24 16:17:14	1	2026-06-22 12:20:22	2026-06-24 16:17:14	5.00	20.00
\.


--
-- TOC entry 3516 (class 0 OID 18080)
-- Dependencies: 229
-- Data for Name: encomiendas; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.encomiendas (id_encomienda, remitente, destinatario, ciudad_destino, peso, dimensiones, descripcion, estado, id_zona, fecha_ingreso, created_at, updated_at, imagen) FROM stdin;
SHL-2026-06-000012	fabrizio	fredy	Abancay	15.00	30x30x30	libros	despachado	1	2026-06-22 10:40:26	2026-06-22 10:40:26	2026-06-24 11:01:18	encomiendas/ca6aySvYsjfDVk9Zl0Y5uGgBOXUrlIVJBbJlVRnx.jpg
SHL-2026-06-000001	Juan Perez	Maria Garcia	Lima	2.50	20x15x10	Ropa	tiempo_excedido	1	2026-06-22 07:20:22	2026-06-22 07:20:22	2026-06-24 19:00:02	\N
SHL-2026-06-000002	Carlos Lopez	Ana Torres	Cusco	8.00	30x25x20	Libros	tiempo_excedido	1	2026-06-22 07:20:22	2026-06-22 07:20:22	2026-06-24 19:00:02	\N
SHL-2026-06-000003	Luis Mamani	Rosa Quispe	Arequipa	25.00	50x40x30	Electrodomestico	tiempo_excedido	1	2026-06-22 07:20:22	2026-06-22 07:20:22	2026-06-24 19:00:02	\N
SHL-2026-06-000004	Pedro Flores	Carmen Huanca	Puno	1.20	15x10x8	Documentos	tiempo_excedido	1	2026-06-22 07:20:22	2026-06-22 07:20:22	2026-06-24 19:00:02	\N
SHL-2026-06-000005	Jose Condori	Elena Vargas	Tacna	15.00	40x30x25	Herramientas	tiempo_excedido	1	2026-06-22 07:20:22	2026-06-22 07:20:22	2026-06-24 19:00:02	\N
SHL-2026-06-000009	Roberto Quispe	Sandra Lima	Lima	3.00	20x15x10	Documentos	tiempo_excedido	1	2026-06-22 09:23:37	2026-06-22 09:23:37	2026-06-24 19:00:02	\N
SHL-2026-06-000010	Miguel Torres	Carmen Puno	Puno	9.00	35x25x20	Ropa	tiempo_excedido	1	2026-06-22 09:23:37	2026-06-22 09:23:37	2026-06-24 19:00:02	\N
SHL-2026-06-000011	Diana Flores	Jorge Cusco	Cusco	22.00	45x35x25	Electrodomestico	tiempo_excedido	1	2026-06-22 09:23:37	2026-06-22 09:23:37	2026-06-24 19:00:02	\N
SHL-2026-06-000008	Diana Flores	Jorge Cusco	Cusco	22.00	45x35x25	Electrodomestico	tiempo_excedido	1	2026-06-12 09:23:37	2026-06-22 09:23:21	2026-06-24 19:00:02	\N
SHL-2026-06-000007	Miguel Torres	Carmen Puno	Puno	9.00	35x25x20	Ropa	tiempo_excedido	1	2026-06-12 09:23:37	2026-06-22 09:23:21	2026-06-24 19:00:02	\N
SHL-2026-06-000006	Roberto Quispe	Sandra Lima	Lima	3.00	20x15x10	Documentos	tiempo_excedido	3	2026-06-12 09:23:37	2026-06-22 09:23:21	2026-06-24 19:00:02	\N
\.


--
-- TOC entry 3513 (class 0 OID 18056)
-- Dependencies: 226
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 3520 (class 0 OID 18109)
-- Dependencies: 233
-- Data for Name: historial_movimientos; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.historial_movimientos (id, id_encomienda, estado_anterior, estado_nuevo, observacion, id_usuario, created_at) FROM stdin;
1	SHL-2026-06-000001	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 07:20:22
2	SHL-2026-06-000002	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 07:20:22
3	SHL-2026-06-000003	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 07:20:22
4	SHL-2026-06-000004	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 07:20:22
5	SHL-2026-06-000005	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 07:20:22
6	SHL-2026-06-000006	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:21
7	SHL-2026-06-000007	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:21
8	SHL-2026-06-000008	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:21
9	SHL-2026-06-000009	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:37
10	SHL-2026-06-000010	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:37
11	SHL-2026-06-000011	recibido	clasificado	Registro y clasificacion automatica	1	2026-06-22 09:23:37
12	SHL-2026-06-000006	tiempo_excedido	en_espera	Alerta resuelta: 	3	2026-06-22 09:24:30
13	SHL-2026-06-000007	tiempo_excedido	en_espera	Alerta resuelta: hola	3	2026-06-22 09:50:22
20	SHL-2026-06-000012	recibido	clasificado	Registro y clasificacion automatica	2	2026-06-22 10:40:26
21	SHL-2026-06-000008	tiempo_excedido	en_espera	Alerta resuelta: hola	3	2026-06-22 10:44:41
22	SHL-2026-06-000007	en_espera	clasificado	\N	2	2026-06-22 10:45:14
23	SHL-2026-06-000006	tiempo_excedido	en_espera	Reubicación física completada	2	2026-06-22 11:17:55
24	SHL-2026-06-000012	clasificado	despachado	Encomienda despachada	2	2026-06-24 11:01:18
\.


--
-- TOC entry 3511 (class 0 OID 18048)
-- Dependencies: 224
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 3510 (class 0 OID 18039)
-- Dependencies: 223
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 3502 (class 0 OID 17989)
-- Dependencies: 215
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_06_15_150710_add_rol_estado_to_users_table	1
5	2026_06_15_150710_create_zonas_table	1
6	2026_06_15_150711_create_encomiendas_table	1
7	2026_06_15_150715_create_alertas_table	1
8	2026_06_15_150716_create_historial_movimientos_table	1
9	2026_06_15_150717_create_configuracion_table	1
10	2026_06_16_001424_add_criterios_peso_to_configuracion_table	1
11	2026_06_19_235554_add_id_padre_to_historial_movimientos_table	1
12	2026_06_20_001811_remove_id_padre_from_historial_movimientos_table	1
13	2026_06_20_014422_create_arbol_almacen_table	1
14	2026_06_22_125324_create_bst_encomiendas_table	2
15	2026_06_22_145326_add_imagen_to_encomiendas_table	3
\.


--
-- TOC entry 3505 (class 0 OID 18006)
-- Dependencies: 218
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 3506 (class 0 OID 18013)
-- Dependencies: 219
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
VXbBpr3Uu0Qmtn0i5rEOtZD6NT8Yo0wvIaGekaub	1	201.240.180.184	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoid0ZmeFF4dVZXZWhJUHRaRzhINFNkUmJzSXRsV2hxeVZSNDFpZkhPOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHBzOi8vc2hhbG9tLnRlY3N1cDIwLnNpdGUvZW5jb21pZW5kYXMiO3M6NToicm91dGUiO3M6MTc6ImVuY29taWVuZGFzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9	1782344890
jZbZlHcQO3ghaDz564i15G4d23Jfdm9um9LQjf6E	\N	135.237.125.223	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoiTnNYOUtNYTVZTUN6TWZpdGVNRmtaUTB2Zm1MdXBybkRBazJzQVpTSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjg6Imh0dHBzOi8vMTYxLjEzMi42OC4xMDEvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782337056
V5MbVxIOiKGk9ClNbhsFoDWqAZuH6Sw7cuu7KHpJ	\N	159.65.168.103	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoib3NONjZsUEF0djgwRm5hQW5QbHQzWUFINnNZODBWczNCYjdHNEZ1ayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xNjEuMTMyLjY4LjEwMSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1782339051
Z9FCtpAVvyr48XdeXG4t7h23DFtlCpKHUpZynHTx	\N	113.31.186.195	Mozilla/5.0 (Linux; Android 5.1.1; vivo X7 Build/LMY47V; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044204 Mobile Safari/537.36 MicroMessenger/6.6.7.1321(0x26060739) NetType/WIFI Language/zh_CN	YTozOntzOjY6Il90b2tlbiI7czo0MDoiOVg4VnExMHV5bDhNbXFYTnMwT1l0aU1ZamlueWJpYW1JQTJGVWNZTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjg6Imh0dHBzOi8vc2hhbG9tLnRlY3N1cDIwLnNpdGUiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782342018
BILrYR6g2vToprsPFtnueZKTpSbbQvXWpFr8Mx6p	\N	43.165.198.144	Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1	YTozOntzOjY6Il90b2tlbiI7czo0MDoicldHM1p6TXlIMHJsV2hSRFNzTXZQMHlYM01CZHpTMDhNYjV1bUQxNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xNjEuMTMyLjY4LjEwMS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782342674
G80KvYDwB2PBJ43R2mZbh8KLJGA25MT9MRNaNlIF	\N	43.166.250.187	Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1	YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ3EyRHZqY3JYdGVlREVKNXlaT0N0NmxCSkx5bFBQdjJTclc2UGJJQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xNjEuMTMyLjY4LjEwMS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782336893
gSeZY9iOzrMOcXz0tVWB31ecu5ri2RfzS8YckLv5	\N	135.237.125.223	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoidDYzdEZuaGhnb3dUOUhYUFQ0aXNYdlM0SmU2VXpXNU9RcXVIUXZzMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTYxLjEzMi42OC4xMDEiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782337056
\.


--
-- TOC entry 3504 (class 0 OID 17996)
-- Dependencies: 217
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, rol, estado) FROM stdin;
1	Administrador	admin@shalom.com	\N	$2y$12$Kma0KPzzL1Qnkmjk7IP7Fuz/WhePF7cwngX0tJ8PRQwo2elQw4UPy	\N	2026-06-22 12:20:21	2026-06-22 12:20:21	administrador	activo
2	Operario	operario@shalom.com	\N	$2y$12$qUhuG6kRlaIDrUFQm2YhyeiblLpPv0EPjc/I/h1e1v/vDtz.ithBy	\N	2026-06-22 12:20:21	2026-06-22 12:20:21	operario	activo
3	Supervisor	supervisor@shalom.com	\N	$2y$12$u40DAGQ6q70jkcIZR6TlW.1aXc08jRY5LU7dpGOWq09ae.lqbsArW	\N	2026-06-22 12:20:21	2026-06-22 12:20:21	supervisor	activo
4	fabrizio	fabrizio@shalom.com	\N	$2y$12$JjO0cJwx9yG/E7VQQ1cWkOlD3qkfsA0AbaJAD0SlWIbvvr3Rmsj4e	\N	2026-06-24 16:02:33	2026-06-24 23:47:32	administrador	activo
\.


--
-- TOC entry 3515 (class 0 OID 18070)
-- Dependencies: 228
-- Data for Name: zonas; Type: TABLE DATA; Schema: public; Owner: fabrizio
--

COPY public.zonas (id, nombre, capacidad, estado, created_at, updated_at) FROM stdin;
2	Zona B	15	disponible	2026-06-22 12:20:22	2026-06-22 12:20:22
3	Zona C	10	parcialmente_ocupada	2026-06-22 12:20:22	2026-06-22 12:20:22
4	Zona D	20	disponible	2026-06-24 15:57:59	2026-06-24 15:57:59
1	Zona A	20	parcialmente_ocupada	2026-06-22 12:20:22	2026-06-22 12:20:22
\.


--
-- TOC entry 3542 (class 0 OID 0)
-- Dependencies: 230
-- Name: alertas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.alertas_id_seq', 14, true);


--
-- TOC entry 3543 (class 0 OID 0)
-- Dependencies: 236
-- Name: arbol_almacen_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.arbol_almacen_id_seq', 624, true);


--
-- TOC entry 3544 (class 0 OID 0)
-- Dependencies: 238
-- Name: bst_encomiendas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.bst_encomiendas_id_seq', 487, true);


--
-- TOC entry 3545 (class 0 OID 0)
-- Dependencies: 234
-- Name: configuracion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.configuracion_id_seq', 1, true);


--
-- TOC entry 3546 (class 0 OID 0)
-- Dependencies: 225
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 3547 (class 0 OID 0)
-- Dependencies: 232
-- Name: historial_movimientos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.historial_movimientos_id_seq', 24, true);


--
-- TOC entry 3548 (class 0 OID 0)
-- Dependencies: 222
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 3549 (class 0 OID 0)
-- Dependencies: 214
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.migrations_id_seq', 15, true);


--
-- TOC entry 3550 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- TOC entry 3551 (class 0 OID 0)
-- Dependencies: 227
-- Name: zonas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: fabrizio
--

SELECT pg_catalog.setval('public.zonas_id_seq', 4, true);


--
-- TOC entry 3339 (class 2606 OID 18102)
-- Name: alertas alertas_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.alertas
    ADD CONSTRAINT alertas_pkey PRIMARY KEY (id);


--
-- TOC entry 3345 (class 2606 OID 18160)
-- Name: arbol_almacen arbol_almacen_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.arbol_almacen
    ADD CONSTRAINT arbol_almacen_pkey PRIMARY KEY (id);


--
-- TOC entry 3347 (class 2606 OID 18174)
-- Name: bst_encomiendas bst_encomiendas_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.bst_encomiendas
    ADD CONSTRAINT bst_encomiendas_pkey PRIMARY KEY (id);


--
-- TOC entry 3322 (class 2606 OID 18036)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 3319 (class 2606 OID 18028)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 3343 (class 2606 OID 18136)
-- Name: configuracion configuracion_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.configuracion
    ADD CONSTRAINT configuracion_pkey PRIMARY KEY (id);


--
-- TOC entry 3337 (class 2606 OID 18093)
-- Name: encomiendas encomiendas_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.encomiendas
    ADD CONSTRAINT encomiendas_pkey PRIMARY KEY (id_encomienda);


--
-- TOC entry 3329 (class 2606 OID 18064)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 3331 (class 2606 OID 18066)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 3341 (class 2606 OID 18117)
-- Name: historial_movimientos historial_movimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.historial_movimientos
    ADD CONSTRAINT historial_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 3327 (class 2606 OID 18054)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 3324 (class 2606 OID 18046)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 3306 (class 2606 OID 17994)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 3312 (class 2606 OID 18012)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 3315 (class 2606 OID 18019)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 3308 (class 2606 OID 18005)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 3310 (class 2606 OID 18003)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3333 (class 2606 OID 18079)
-- Name: zonas zonas_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.zonas
    ADD CONSTRAINT zonas_nombre_unique UNIQUE (nombre);


--
-- TOC entry 3335 (class 2606 OID 18077)
-- Name: zonas zonas_pkey; Type: CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.zonas
    ADD CONSTRAINT zonas_pkey PRIMARY KEY (id);


--
-- TOC entry 3317 (class 1259 OID 18029)
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: fabrizio
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- TOC entry 3320 (class 1259 OID 18037)
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: fabrizio
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- TOC entry 3325 (class 1259 OID 18047)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: fabrizio
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 3313 (class 1259 OID 18021)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: fabrizio
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 3316 (class 1259 OID 18020)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: fabrizio
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 3357 (class 2620 OID 18167)
-- Name: encomiendas trg_arbol_almacen; Type: TRIGGER; Schema: public; Owner: fabrizio
--

CREATE TRIGGER trg_arbol_almacen AFTER INSERT OR DELETE OR UPDATE ON public.encomiendas FOR EACH STATEMENT EXECUTE FUNCTION public.trigger_actualizar_arbol();


--
-- TOC entry 3358 (class 2620 OID 18193)
-- Name: encomiendas trg_bst_encomiendas; Type: TRIGGER; Schema: public; Owner: fabrizio
--

CREATE TRIGGER trg_bst_encomiendas AFTER INSERT OR DELETE OR UPDATE ON public.encomiendas FOR EACH STATEMENT EXECUTE FUNCTION public.trigger_actualizar_bst();


--
-- TOC entry 3349 (class 2606 OID 18103)
-- Name: alertas alertas_id_encomienda_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.alertas
    ADD CONSTRAINT alertas_id_encomienda_foreign FOREIGN KEY (id_encomienda) REFERENCES public.encomiendas(id_encomienda);


--
-- TOC entry 3354 (class 2606 OID 18161)
-- Name: arbol_almacen arbol_almacen_id_padre_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.arbol_almacen
    ADD CONSTRAINT arbol_almacen_id_padre_foreign FOREIGN KEY (id_padre) REFERENCES public.arbol_almacen(id);


--
-- TOC entry 3355 (class 2606 OID 18180)
-- Name: bst_encomiendas bst_encomiendas_id_derecho_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.bst_encomiendas
    ADD CONSTRAINT bst_encomiendas_id_derecho_foreign FOREIGN KEY (id_derecho) REFERENCES public.bst_encomiendas(id);


--
-- TOC entry 3356 (class 2606 OID 18175)
-- Name: bst_encomiendas bst_encomiendas_id_izquierdo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.bst_encomiendas
    ADD CONSTRAINT bst_encomiendas_id_izquierdo_foreign FOREIGN KEY (id_izquierdo) REFERENCES public.bst_encomiendas(id);


--
-- TOC entry 3352 (class 2606 OID 18142)
-- Name: configuracion configuracion_id_admin_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.configuracion
    ADD CONSTRAINT configuracion_id_admin_foreign FOREIGN KEY (id_admin) REFERENCES public.users(id);


--
-- TOC entry 3353 (class 2606 OID 18137)
-- Name: configuracion configuracion_id_zona_reubicacion_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.configuracion
    ADD CONSTRAINT configuracion_id_zona_reubicacion_foreign FOREIGN KEY (id_zona_reubicacion) REFERENCES public.zonas(id);


--
-- TOC entry 3348 (class 2606 OID 18087)
-- Name: encomiendas encomiendas_id_zona_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.encomiendas
    ADD CONSTRAINT encomiendas_id_zona_foreign FOREIGN KEY (id_zona) REFERENCES public.zonas(id);


--
-- TOC entry 3350 (class 2606 OID 18118)
-- Name: historial_movimientos historial_movimientos_id_encomienda_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.historial_movimientos
    ADD CONSTRAINT historial_movimientos_id_encomienda_foreign FOREIGN KEY (id_encomienda) REFERENCES public.encomiendas(id_encomienda);


--
-- TOC entry 3351 (class 2606 OID 18123)
-- Name: historial_movimientos historial_movimientos_id_usuario_foreign; Type: FK CONSTRAINT; Schema: public; Owner: fabrizio
--

ALTER TABLE ONLY public.historial_movimientos
    ADD CONSTRAINT historial_movimientos_id_usuario_foreign FOREIGN KEY (id_usuario) REFERENCES public.users(id);


-- Completed on 2026-06-24 19:00:08 -05

--
-- PostgreSQL database dump complete
--

\unrestrict 2Q7SLEpVwEtuA65CTwl4ODuSYifBSYD1AUK9Cc6qP61Kuad5Iip72Ubrhv4doQ5

