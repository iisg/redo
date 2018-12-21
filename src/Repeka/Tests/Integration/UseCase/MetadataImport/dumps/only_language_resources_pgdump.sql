--
-- PostgreSQL database dump
--

-- Dumped from database version 10.2
-- Dumped by pg_dump version 10.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: audit; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE audit (
    id integer NOT NULL,
    user_id integer,
    commandname character varying(64) NOT NULL,
    data jsonb NOT NULL,
    successful boolean NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE audit OWNER TO postgres;

--
-- Name: COLUMN audit.data; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN audit.data IS '(DC2Type:jsonb)';


--
-- Name: COLUMN audit.created_at; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN audit.created_at IS '(DC2Type:datetimetz_immutable)';


--
-- Name: audit_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE audit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE audit_id_seq OWNER TO postgres;

--
-- Name: endpoint_usage_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE endpoint_usage_log (
    id integer NOT NULL,
    resource_id integer,
    url character varying(255) NOT NULL,
    client_ip character varying(255) NOT NULL,
    usage_date_time timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usage_key character varying(255) NOT NULL
);


ALTER TABLE endpoint_usage_log OWNER TO postgres;

--
-- Name: endpoint_usage_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE endpoint_usage_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE endpoint_usage_log_id_seq OWNER TO postgres;

--
-- Name: language; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE language (
    flag character varying(10) NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(10) NOT NULL
);


ALTER TABLE language OWNER TO postgres;

--
-- Name: metadata; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE metadata (
    id integer NOT NULL,
    control character varying(25) NOT NULL,
    label jsonb NOT NULL,
    description jsonb DEFAULT '{}'::jsonb NOT NULL,
    placeholder jsonb DEFAULT '{}'::jsonb NOT NULL,
    name character varying(255),
    base_id integer,
    ordinal_number integer DEFAULT 0 NOT NULL,
    parent_id integer,
    constraints jsonb NOT NULL,
    shown_in_brief boolean NOT NULL,
    resource_class character varying(64) DEFAULT NULL::character varying NOT NULL,
    copy_to_child_resource boolean DEFAULT false NOT NULL,
    group_id character varying(64) DEFAULT 'DEFAULT_GROUP'::character varying NOT NULL
);


ALTER TABLE metadata OWNER TO postgres;

--
-- Name: metadata_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE metadata_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE metadata_id_seq OWNER TO postgres;

--
-- Name: migration_versions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE migration_versions (
    version character varying(255) NOT NULL
);


ALTER TABLE migration_versions OWNER TO postgres;

--
-- Name: resource; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE resource (
    id integer NOT NULL,
    kind_id integer,
    contents jsonb NOT NULL,
    marking jsonb,
    resource_class character varying(64) DEFAULT NULL::character varying NOT NULL,
    display_strategy_dependencies jsonb DEFAULT '{}'::jsonb NOT NULL,
    display_strategies_dirty boolean DEFAULT true NOT NULL
);


ALTER TABLE resource OWNER TO postgres;

--
-- Name: COLUMN resource.display_strategy_dependencies; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN resource.display_strategy_dependencies IS '(DC2Type:jsonb)';


--
-- Name: resource_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE resource_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE resource_id_seq OWNER TO postgres;

--
-- Name: resource_kind; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE resource_kind (
    id integer NOT NULL,
    label jsonb NOT NULL,
    workflow_id integer,
    resource_class character varying(64) DEFAULT NULL::character varying NOT NULL,
    metadata_list jsonb NOT NULL
);


ALTER TABLE resource_kind OWNER TO postgres;

--
-- Name: resource_kind_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE resource_kind_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE resource_kind_id_seq OWNER TO postgres;

--
-- Name: user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "user" (
    id integer NOT NULL,
    password character varying(64),
    is_active boolean NOT NULL,
    user_data_id integer NOT NULL,
    roles jsonb DEFAULT '[]'::jsonb NOT NULL
);


ALTER TABLE "user" OWNER TO postgres;

--
-- Name: COLUMN "user".roles; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN "user".roles IS '(DC2Type:jsonb)';


--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_id_seq OWNER TO postgres;

--
-- Name: workflow; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE workflow (
    id integer NOT NULL,
    name jsonb NOT NULL,
    places jsonb DEFAULT '[]'::jsonb NOT NULL,
    transitions jsonb DEFAULT '[]'::jsonb NOT NULL,
    diagram text,
    thumbnail text,
    resource_class character varying(64) DEFAULT NULL::character varying NOT NULL
);


ALTER TABLE workflow OWNER TO postgres;

--
-- Name: workflow_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE workflow_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE workflow_id_seq OWNER TO postgres;

--
-- Data for Name: audit; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: endpoint_usage_log; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: language; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO language VALUES ('GB', 'angielski', 'GB');
INSERT INTO language VALUES ('PL', 'polski', 'PL');


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO metadata VALUES (139, 'text', '{"PL": "Nazwa kodowa"}', '[]', '[]', 'nazwa_kodowa', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (143, 'text', '{"PL": "Nazwa"}', '[]', '[]', 'nazwa', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (145, 'text', '{"PL": "Skrót"}', '[]', '[]', 'skrot', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (105, 'text', '{"PL": "rodzaj saknera"}', '[]', '[]', 'rodzaj_saknera', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-5, 'display-strategy', '{"EN": "Label", "PL": "Etykieta"}', '[]', '[]', 'label', NULL, -1, NULL, '{"displayStrategy": "#{{ r.id }}"}', true, '', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (151, 'text', '{"PL": "Link"}', '[]', '[]', 'link', NULL, -1, NULL, '{"regex": ""}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (155, 'wysiwyg-editor', '{"PL": "Treść strony"}', '[]', '[]', 'tresc_strony', NULL, -1, NULL, '{"maxCount": 1}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (162, 'relationship', '{"PL": "Język tytułu"}', '[]', '[]', 'jezyk_tytulu', NULL, -1, 3, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (163, 'relationship', '{"PL": "Język"}', '[]', '[]', 'jezyk_wariantu_tytulu', NULL, -1, 61, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (167, 'relationship', '{"PL": "Język"}', '[]', '[]', 'jezyk_tytulu_tomu', NULL, -1, 120, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (168, 'text', '{"PL": "ID Koha"}', '[]', '[]', 'id_koha', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (176, 'text', '{"PL": "Rodzaj współpracy"}', '[]', '[]', NULL, 175, -1, 63, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (180, 'text', '{"PL": "NUKAT ID"}', '[]', '[]', NULL, 178, -1, 4, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (21, 'text', '{"PL": "symbol jednostki"}', '[]', '[]', 'symbol_jednostki', NULL, -1, NULL, '{}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (20, 'text', '{"PL": "nazwa jednostki"}', '[]', '[]', 'nazwa_jednostki', NULL, -1, NULL, '{}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (140, 'text', '{"PL": "Kod ISO"}', '[]', '[]', 'kod_iso', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (144, 'text', '{"PL": "Old ID"}', '[]', '[]', 'old_id', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (146, 'text', '{"PL": "Skrót jednoznakowy"}', '[]', '[]', 'skrot_jednoznakowy', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (185, 'boolean', '{"PL": "Data nieznana"}', '[]', '[]', 'dta_nieznana', NULL, -1, 147, '{"maxCount": 1}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (34, 'text', '{"PL": "Identyfikator wydziału"}', '[]', '[]', 'identyfikator_wydzialu', NULL, 10, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (35, 'text', '{"PL": "Instytut"}', '[]', '[]', 'instytut', NULL, 11, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (36, 'text', '{"PL": "Identyfikator instytutu"}', '[]', '[]', 'identyfikator_instytutu', NULL, 12, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (28, 'text', '{"PL": "Email"}', '[]', '[]', 'email', NULL, 4, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (29, 'text', '{"PL": "Miasto"}', '[]', '[]', 'miasto', NULL, 5, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (148, 'display-strategy', '{"PL": "Imported file template"}', '[]', '[]', 'imported_file_template', NULL, -1, NULL, '[]', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (30, 'text', '{"PL": "Ulica"}', '[]', '[]', 'ulica', NULL, 6, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (31, 'text', '{"PL": "Pesel"}', '[]', '[]', 'pesel', NULL, 7, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (152, 'text', '{"PL": "Nazwa ikony"}', '[]', '[]', 'nazwa_ikony', NULL, -1, NULL, '{"regex": "", "maxCount": 1}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (32, 'text', '{"PL": "Kategoria"}', '[]', '[]', 'kategoria', NULL, 8, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (27, 'text', '{"PL": "Nazwisko"}', '[]', '[]', 'nazwisko', NULL, 2, NULL, '{"regex": ""}', true, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (26, 'text', '{"PL": "Imię"}', '[]', '[]', 'imie', NULL, 3, NULL, '{"regex": ""}', true, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (73, 'text', '{"PL": "Nazwa grupy"}', '[]', '[]', 'group_name', NULL, -1, NULL, '{"regex": ""}', true, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (78, 'text', '{"PL": "Imię teściowej"}', '[]', '[]', 'imie_tesciowej', NULL, -1, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-3, 'relationship', '{"EN": "Group member", "PL": "Członek grupy"}', '[]', '[]', 'group_member', NULL, -1, NULL, '{"resourceKind": [10]}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (164, 'relationship', '{"PL": "Język"}', '[]', '[]', 'jezyk_tytulu_serii', NULL, -1, 135, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (169, 'text', '{"PL": "UKD"}', '[]', '[]', 'ukd_symbol_klasyfikacji', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (149, 'display-strategy', '{"PL": "Wyrenderowana treść"}', '[]', '{"PL": "Rendered content"}', 'wyrenderowana_tresc', NULL, -1, NULL, '[]', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (153, 'relationship', '{"PL": "Ikona"}', '[]', '[]', 'ikona_linku', NULL, -1, 151, '{"maxCount": 1, "resourceKind": [75], "relatedResourceMetadataFilter": []}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (165, 'text', '{"PL": "Tekst linku"}', '[]', '[]', 'tekst_linku', NULL, -1, 117, '{"regex": "", "maxCount": 1}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (170, 'text', '{"PL": "UKD dopowiedzenie słowne"}', '[]', '[]', 'ukd_dopowiedzenie_slowne', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (183, 'text', '{"PL": "Data zakończenia skanowania"}', '[]', '[]', 'data_zakonczenia_skanowania', NULL, 14, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (136, 'text', '{"PL": "Liczba stron"}', '[]', '[]', 'strony', NULL, 16, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-1, 'relationship', '{"EN": "Parent", "PL": "Rodzic"}', '[]', '[]', 'parent', NULL, -1, NULL, '{}', false, '', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-2, 'text', '{"EN": "Username", "PL": "Nazwa użytkownika"}', '[]', '[]', 'username', NULL, 1, NULL, '{}', true, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (33, 'text', '{"PL": "Wydział"}', '[]', '[]', 'wydzial', NULL, 9, NULL, '{"regex": ""}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (104, 'text', '{"PL": "Nazwa języka"}', '[]', '[]', 'nazwa_jezyka', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (106, 'text', '{"PL": "Opis"}', '[]', '[]', 'opis', NULL, -1, NULL, '{"regex": ""}', true, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (103, 'text', '{"PL": "Rozmiar rozdzielczości"}', '{"PL": "Rozmiar rozdzielczości"}', '[]', 'rozmiar_rozdzielczosci', NULL, -1, NULL, '{"regex": ""}', true, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (121, 'text', '{"PL": "Sekcja czasopisma"}', '{"PL": "Sekcja w numerze Czasopisma Technicznego"}', '[]', 'sekcja_czasopisma', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (154, 'text', '{"PL": "Nazwa linku"}', '[]', '[]', 'nazwa_linku', NULL, -1, 151, '{"regex": "", "maxCount": 1}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (142, 'text', '{"PL": "Nazwa licencji"}', '[]', '[]', 'nazwa_licencji', NULL, -1, NULL, '{"regex": ""}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (150, 'text', '{"PL": "Tytuł strony"}', '[]', '{"PL": ""}', 'tytul_strony', NULL, -1, NULL, '{"regex": "", "maxCount": 1}', false, 'cms', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (166, 'relationship', '{"PL": "Język"}', '[]', '[]', 'jezyk_slow_kluczowych', NULL, -1, 116, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (179, 'text', '{"PL": "Koha ID "}', '[]', '[]', NULL, 177, -1, 4, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (22, 'timestamp', '{"PL": "data powstania"}', '[]', '[]', 'data_powstania', NULL, -1, NULL, '{}', false, 'dictionaries', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (147, 'timestamp', '{"PL": "Daty biograficzne"}', '[]', '[]', NULL, 62, -1, 4, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (182, 'timestamp', '{"PL": "Daty biograficzne"}', '[]', '[]', NULL, 62, -1, 63, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-4, 'relationship', '{"GB": "Reproduktor", "PL": "Reproduktor"}', '[]', '[]', 'reproductor', NULL, -1, NULL, '{"maxCount": -1, "resourceKind": [-1, 10], "relatedResourceMetadataFilter": []}', false, '', true, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (101, 'timestamp', '{"PL": "Data utworzenia rekordu"}', '{"PL": "wypełniana automatycznie"}', '[]', 'data_utworzenia_rekordu', NULL, 28, NULL, '{"maxCount": 1}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (102, 'text', '{"PL": "Opis problemów ze skanami"}', '{"PL": "Wypełniany podczas obróbki gdy wykryte zostaną błędy"}', '[]', 'opis_problemow_ze_skanami', NULL, 29, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (222, 'boolean', '{"GB": "SUB_BOOL", "PL": "SUB_BOOL"}', '[]', '[]', NULL, 217, -1, 220, '[]', false, 'books', false, 'basic');
INSERT INTO metadata VALUES (221, 'integer', '{"GB": "SUB", "PL": "SUB"}', '[]', '[]', 'sub', NULL, -1, 220, '{"minMaxValue": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (181, 'text', '{"PL": "Oznaczenie śmierci autora/Uwaga o udostępnieniu"}', '[]', '[]', 'uwaga_o_udostepnieniu', NULL, 21, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (212, 'integer', '{"GB": "TEST_INTEGER", "PL": "TEST_INTEGER"}', '[]', '[]', 'test_integer', NULL, 12, NULL, '{"minMaxValue": {"max": 10, "min": 5}}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (223, 'integer', '{"GB": "test", "PL": "test"}', '[]', '[]', 'test', NULL, 1, NULL, '{"minMaxValue": {"min": 100}}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (209, 'relationship', '{"GB": "Resource creator", "PL": "Twórca zasobu"}', '[]', '[]', 'tworca_zasobu', NULL, 2, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (206, 'double', '{"GB": "REAL NUMBER ENG", "PL": "REAL NUMBER"}', '[]', '[]', 'real_number', NULL, 3, NULL, '{"doublePrecision": 100}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (197, 'display-strategy', '{"GB": "asdsad", "PL": "asdsad"}', '[]', '[]', 'asdsad', NULL, 5, NULL, '[]', false, 'books', false, 'basic');
INSERT INTO metadata VALUES (220, 'textarea', '{"GB": "TEST_SUBMETA", "PL": "TEST_SUBMETA"}', '[]', '[]', 'test_submeta', NULL, 6, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (219, 'file', '{"GB": "TEST_FILE", "PL": "TEST_FILE"}', '[]', '[]', 'test_file', NULL, 7, NULL, '{"maxCount": 2}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (217, 'boolean', '{"GB": "TEST_BOOL", "PL": "TEST_BOOL"}', '[]', '[]', 'test_bool', NULL, 8, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (216, 'timestamp', '{"GB": "TEST_MARK", "PL": "TEST_MARK"}', '[]', '[]', 'test_mark', NULL, 9, NULL, '[]', false, 'books', false, 'basic');
INSERT INTO metadata VALUES (215, 'flexible-date', '{"GB": "TEST_DATE", "PL": "TEST_DATE"}', '[]', '[]', 'test_date', NULL, 10, NULL, '{"maxCount": 1}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (214, 'double', '{"GB": "TEST_REAL", "PL": "TEST_REAL"}', '{"PL": ""}', '[]', 'test_real', NULL, 11, NULL, '{"doublePrecision": 3}', false, 'books', false, 'cms');
INSERT INTO metadata VALUES (211, 'text', '{"GB": "TEST_TEKST", "PL": "TEST_TEKST"}', '[]', '[]', 'test_tekst', NULL, 0, NULL, '{"regex": "xyz", "maxCount": 2}', false, 'books', false, 'basic');
INSERT INTO metadata VALUES (218, 'relationship', '{"GB": "TEST_RELATION", "PL": "TEST_RELATION"}', '[]', '[]', 'test_relation', NULL, 4, NULL, '{"resourceKind": [1], "relatedResourceMetadataFilter": {"135": "aa"}}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (127, 'file', '{"PL": "Plik zasobu"}', '[]', '[]', 'zasob_plik', NULL, 13, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (135, 'text', '{"PL": "Tytuł serii"}', '[]', '[]', 'tytul_serii', NULL, 15, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (157, 'text', '{"PL": "Format książki"}', '[]', '[]', 'format_projekt', NULL, 17, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (161, 'relationship', '{"PL": "Lokalizacja skanera Avision FB2280E"}', '[]', '[]', 'avision', NULL, 18, NULL, '{"resourceKind": [63], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (173, 'text', '{"PL": "Koha ID"}', '[]', '[]', 'koha_id', NULL, 19, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (177, 'text', '{"PL": "Koha ID "}', '[]', '[]', 'koha_id_autor', NULL, 20, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (129, 'text', '{"PL": "Strony"}', '[]', '[]', 'zakres_stron', NULL, 23, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (133, 'relationship', '{"PL": "Promotor pomocniczy"}', '[]', '[]', 'promotor_pomocniczy', NULL, 24, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (137, 'text', '{"PL": "Numer w serii"}', '[]', '[]', 'nr_seria', NULL, 25, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (158, 'text', '{"PL": "Załączniki"}', '[]', '[]', 'zalaczniki_projekt', NULL, 26, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (178, 'text', '{"PL": "NUKAT ID"}', '[]', '[]', 'nukat_id_autor', NULL, 27, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (126, 'relationship', '{"PL": "Klasyfikacja"}', '[]', '[]', 'klasyfikacja', NULL, 30, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (108, 'text', '{"PL": "Grupa uprawnionych korektorów"}', '[]', '[]', 'grupa_uprawnionych_korektorow', NULL, 31, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (109, 'relationship', '{"PL": "Grupa uprawnionych korektorów"}', '[]', '[]', 'uprawnionych_korektorow_grupa', NULL, 32, NULL, '{"resourceKind": [10], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (110, 'text', '{"PL": "Opublikowane w"}', '[]', '[]', 'zrodlo', NULL, 33, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (111, 'text', '{"PL": "Numeracja"}', '[]', '[]', 'numeracja_w_czasopismie', NULL, 34, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (113, 'text', '{"PL": "ISSN"}', '[]', '[]', 'issn', NULL, 35, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (114, 'text', '{"PL": "DOI"}', '[]', '[]', 'doi', NULL, 36, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (115, 'text', '{"PL": "Abstrakt"}', '[]', '[]', 'abstrakt', NULL, 37, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (116, 'text', '{"PL": "Słowa kluczowe"}', '[]', '[]', 'slowa_kluczowe', NULL, 38, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (117, 'text', '{"PL": "Link do katalogu BPK"}', '[]', '[]', 'link_do_katalogu', NULL, 39, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (125, 'text', '{"PL": "UKD"}', '[]', '[]', 'ukd', NULL, 22, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (118, 'text', '{"PL": "Link do Bibliografii Publikacji Pracownikó PK"}', '[]', '[]', 'link_do_bpp', NULL, 40, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (119, 'text', '{"PL": "Link do publikacji"}', '[]', '[]', 'link_do_oryginalu', NULL, 41, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (107, 'relationship', '{"PL": "Rozdzielczość"}', '[]', '[]', 'rozdzielczosc', NULL, 42, NULL, '{"resourceKind": [12], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (120, 'text', '{"PL": "Tytuł tomu"}', '[]', '[]', 'tytul_tomu', NULL, 43, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (130, 'relationship', '{"PL": "Promotor"}', '[]', '[]', 'promotor', NULL, 44, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (134, 'relationship', '{"PL": "Status pracy dyplomowej"}', '[]', '[]', 'status_pracy_dyplomowej', NULL, 45, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (138, 'relationship', '{"PL": "Zobacz też"}', '[]', '[]', 'zobacz_tez', NULL, 46, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (122, 'relationship', '{"PL": "Sekcja czasopisma"}', '[]', '[]', 'sekcja_czasopisma', NULL, 47, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (159, 'text', '{"PL": "Dodatkowe informacje"}', '[]', '[]', 'dodatkowe_informacje_projekt', NULL, 48, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (175, 'text', '{"PL": "Rodzaj współpracy"}', '[]', '[]', 'rodzaj_wspolpracy', NULL, 49, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (123, 'relationship', '{"PL": "Prawa dostępu"}', '[]', '[]', 'prawa_dostepu', NULL, 50, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (1000000, 'text', '{"EN": "Metadana_do_testowania_automatycznego1", "PL": "Metadana_do_testowania_automatycznego1"}', '[]', '[]', 'metadana_do_testowania_automatycznego1', NULL, 51, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (156, 'text', '{"PL": "Identyfikator NUKAT"}', '[]', '[]', 'identyfikator_nukat', NULL, 53, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (1000001, 'text', '{"EN": "Metadana_do_testowania_automatycznego2", "PL": "Metadana_do_testowania_automatycznego2"}', '[]', '[]', 'metadana_do_testowania_automatycznego2', NULL, 52, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (1000002, 'text', '{"EN": "Metadana_do_testowania_automatycznego3", "PL": "Metadana_do_testowania_automatycznego3"}', '[]', '[]', 'metadana_do_testowania_automatycznego3', NULL, 54, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (160, 'relationship', '{"PL": "Skaner"}', '[]', '[]', 'skaner', NULL, 55, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (172, 'text', '{"PL": "Miejsce druku"}', '[]', '[]', 'miejsce_druku', NULL, 56, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (124, 'timestamp', '{"PL": "Data utworzenia"}', '[]', '[]', 'data_utworzenia', NULL, 57, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (128, 'file', '{"PL": "Zasób w wersji dla przeglądarki"}', '[]', '[]', 'zasob_folder', NULL, 58, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (132, 'timestamp', '{"PL": "Data obrony"}', '[]', '[]', 'data_obrony', NULL, 59, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (3, 'text', '{"PL": "Tytuł"}', '[]', '[]', 'tytul', NULL, 60, NULL, '{}', true, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (61, 'text', '{"PL": "Wariant tytułu"}', '[]', '[]', 'wariant_tytulu', NULL, 61, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (4, 'text', '{"PL": "Autor"}', '[]', '[]', 'autor', NULL, 62, NULL, '{}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (62, 'timestamp', '{"PL": "Daty biograficzne"}', '[]', '[]', 'daty_bibliograficzne', NULL, 63, NULL, '{}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (63, 'text', '{"PL": "Współtwórca"}', '[]', '[]', 'wspoltworca', NULL, 64, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (5, 'timestamp', '{"EN": "Data", "PL": "Data wydania"}', '[]', '[]', 'data_wydania', NULL, 65, NULL, '{}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (64, 'text', '{"PL": "Miejsce wydania"}', '[]', '[]', 'miejsce_wydania', NULL, 66, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (65, 'text', '{"PL": "Wydawca"}', '[]', '[]', 'wydawca', NULL, 67, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (66, 'text', '{"PL": "Drukarnia"}', '[]', '[]', 'drukarnia', NULL, 68, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (72, 'text', '{"PL": "ISBN"}', '[]', '[]', 'isbn', NULL, 69, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (53, 'relationship', '{"PL": "Język"}', '[]', '[]', 'jezyk', NULL, 70, NULL, '{"resourceKind": [48], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (52, 'text', '{"PL": "Opis"}', '[]', '[]', 'opis', NULL, 71, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (50, 'relationship', '{"PL": "Wydział"}', '[]', '[]', 'wydzial', NULL, 72, NULL, '{"resourceKind": [4], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (67, 'text', '{"PL": "Seria"}', '[]', '[]', 'seria', NULL, 73, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (97, 'relationship', '{"PL": "Licencja "}', '[]', '[]', 'licencja', NULL, 74, NULL, '{"resourceKind": [49], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (99, 'textarea', '{"PL": "Uwagi "}', '[]', '[]', 'uwagi', NULL, 75, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (76, 'text', '{"PL": "Barkod"}', '[]', '[]', 'barkod', NULL, 76, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (79, 'text', '{"PL": "Sygnatura"}', '[]', '[]', 'sygnatura', NULL, 77, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (70, 'text', '{"PL": "Proweniencje"}', '[]', '[]', 'proweniencje', NULL, 78, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (6, 'boolean', '{"EN": "szwabacha", "PL": "szwabacha"}', '[]', '[]', 'szwabacha', NULL, 79, NULL, '{}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (86, 'boolean', '{"PL": "Uwaga o czcionce gotyckiej"}', '[]', '[]', 'uwaga_o_czcionce_gotyckiej_odbitce_litograficznej', NULL, 80, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (94, 'relationship', '{"PL": "Tryb dokumentu (document mode)"}', '[]', '[]', 'tryb_dokumentu', NULL, 81, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (96, 'integer', '{"PL": "Rozmiar katalogu (w GB) "}', '[]', '[]', 'rozmiar_katalogu_w_gb', NULL, 82, NULL, '{"minMaxValue": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (92, 'textarea', '{"PL": "Uwagi dla redaktora"}', '[]', '[]', 'uwagi_dla_redaktora', NULL, 83, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (95, 'relationship', '{"PL": "Tryb skanowania (quality mode) "}', '[]', '[]', 'tryb_skanowania', NULL, 84, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (88, 'relationship', '{"PL": "Format"}', '[]', '[]', 'format', NULL, 85, NULL, '{"resourceKind": [], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (89, 'integer', '{"PL": "Rzeczywista liczba stron"}', '[]', '[]', 'rzeczywista_liczba_stron', NULL, 86, NULL, '{"minMaxValue": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (90, 'text', '{"PL": "Strony poza formatem "}', '[]', '[]', 'strony_poza_formatem', NULL, 87, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (91, 'text', '{"PL": "Załączniki "}', '[]', '[]', 'zalaczniki', NULL, 88, NULL, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (74, 'file', '{"PL": "Okładka - plik"}', '[]', '[]', 'okladka', NULL, 89, NULL, '[]', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (80, 'relationship', '{"PL": "Osoba tworząca rekord "}', '[]', '[]', 'osoba_tworzaca_rekord', NULL, 90, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (81, 'relationship', '{"PL": "Osoba przydzielająca zadanie skanowania "}', '[]', '[]', 'osoba_przydzielajaca_zadanie_skanowania', NULL, 91, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (83, 'relationship', '{"PL": "Osoba skanująca "}', '[]', '[]', 'osoba_skanujaca', NULL, 92, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (82, 'relationship', '{"PL": "Osoba przydzielająca zadanie obróbki graficznej"}', '[]', '[]', 'osoba_przydzielajaca_zadanie_obrobki_graficznej', NULL, 93, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (84, 'relationship', '{"PL": "Osoba wykonująca obróbkę graficzną i wprowadzająca metadane "}', '[]', '[]', 'osoba_wprowadzajaca_metadane', NULL, 94, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (85, 'relationship', '{"PL": "Osoba sprawdzająca poprawność  rekordu "}', '[]', '[]', 'osoba_sprawdzajaca_poprawnosc_rekordu', NULL, 95, NULL, '{"resourceKind": [-1], "relatedResourceMetadataFilter": []}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (246, 'integer', '{"GB": "liczba palców prawej dłoni", "PL": "liczba palców prawej dłoni"}', '[]', '{"GB": "aa", "PL": "aa"}', 'liczba_palcow_prawej_dloni', NULL, -1, NULL, '{"maxCount": -1, "minMaxValue": {"max": 15, "min": 0}}', false, 'users', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (187, 'text', '{"PL": "Koha ID "}', '[]', '[]', 'koha_id_coautor', NULL, 7, 63, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (188, 'text', '{"PL": "NUKAT ID"}', '[]', '[]', NULL, 178, -1, 63, '{"regex": ""}', false, 'books', false, 'DEFAULT_GROUP');


--
-- Data for Name: migration_versions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO migration_versions VALUES ('20161025002809');
INSERT INTO migration_versions VALUES ('20161109090305');
INSERT INTO migration_versions VALUES ('20161115112305');
INSERT INTO migration_versions VALUES ('20161116213649');
INSERT INTO migration_versions VALUES ('20161124095103');
INSERT INTO migration_versions VALUES ('20161129213649');
INSERT INTO migration_versions VALUES ('20161208095050');
INSERT INTO migration_versions VALUES ('20161228112923');
INSERT INTO migration_versions VALUES ('20170102081632');
INSERT INTO migration_versions VALUES ('20170105190654');
INSERT INTO migration_versions VALUES ('20170109113603');
INSERT INTO migration_versions VALUES ('20170123211757');
INSERT INTO migration_versions VALUES ('20170208092729');
INSERT INTO migration_versions VALUES ('20170210084530');
INSERT INTO migration_versions VALUES ('20170222184559');
INSERT INTO migration_versions VALUES ('20170307101932');
INSERT INTO migration_versions VALUES ('20170331073051');
INSERT INTO migration_versions VALUES ('20170404101209');
INSERT INTO migration_versions VALUES ('20170505120015');
INSERT INTO migration_versions VALUES ('20170524094656');
INSERT INTO migration_versions VALUES ('20170524145400');
INSERT INTO migration_versions VALUES ('20170611073853');
INSERT INTO migration_versions VALUES ('20170612233700');
INSERT INTO migration_versions VALUES ('20170623122154');
INSERT INTO migration_versions VALUES ('20170717221928');
INSERT INTO migration_versions VALUES ('20170829182414');
INSERT INTO migration_versions VALUES ('20170906113103');
INSERT INTO migration_versions VALUES ('20170914113000');
INSERT INTO migration_versions VALUES ('20171009081818');
INSERT INTO migration_versions VALUES ('20171201161000');
INSERT INTO migration_versions VALUES ('20171218173622');
INSERT INTO migration_versions VALUES ('20180130084700');
INSERT INTO migration_versions VALUES ('20180202134333');
INSERT INTO migration_versions VALUES ('20180209110309');
INSERT INTO migration_versions VALUES ('20180211110309');
INSERT INTO migration_versions VALUES ('20180211110310');
INSERT INTO migration_versions VALUES ('20180214232445');
INSERT INTO migration_versions VALUES ('20180216235421');
INSERT INTO migration_versions VALUES ('20180219213547');
INSERT INTO migration_versions VALUES ('20180215094822');
INSERT INTO migration_versions VALUES ('20180313122800');
INSERT INTO migration_versions VALUES ('20180321122132');
INSERT INTO migration_versions VALUES ('20180413093410');
INSERT INTO migration_versions VALUES ('20180424133832');
INSERT INTO migration_versions VALUES ('20180424170831');
INSERT INTO migration_versions VALUES ('20180428200114');
INSERT INTO migration_versions VALUES ('20180422131227');
INSERT INTO migration_versions VALUES ('20180503231556');
INSERT INTO migration_versions VALUES ('20180523083737');
INSERT INTO migration_versions VALUES ('20180525085546');
INSERT INTO migration_versions VALUES ('20180528200825');
INSERT INTO migration_versions VALUES ('20180528200826');
INSERT INTO migration_versions VALUES ('20180607104015');
INSERT INTO migration_versions VALUES ('20180612202801');
INSERT INTO migration_versions VALUES ('20180618111703');
INSERT INTO migration_versions VALUES ('20180705073559');
INSERT INTO migration_versions VALUES ('20180718095249');
INSERT INTO migration_versions VALUES ('20180718095315');
INSERT INTO migration_versions VALUES ('20180718100453');
INSERT INTO migration_versions VALUES ('20180726215824');
INSERT INTO migration_versions VALUES ('20180914132558');
INSERT INTO migration_versions VALUES ('20181017143027');
INSERT INTO migration_versions VALUES ('20180914084855');
INSERT INTO migration_versions VALUES ('20181001112427');
INSERT INTO migration_versions VALUES ('20181022210730');
INSERT INTO migration_versions VALUES ('20181023110439');


--
-- Data for Name: resource; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO resource VALUES (520, 48, '{"-1": [{"value": 47}], "-5": [{"value": "bułgarski"}], "139": [{"value": "bulgarski"}], "140": [{"value": "bul"}], "143": [{"value": "bułgarski"}], "144": [{"value": "3667"}]}', NULL, 'dictionaries', '{"520/143": [-5]}', false);
INSERT INTO resource VALUES (521, 48, '{"-1": [{"value": 47}], "-5": [{"value": "chorwacki"}], "139": [{"value": "chorwacki"}], "140": [{"value": "hrv"}], "143": [{"value": "chorwacki"}], "144": [{"value": "3813"}]}', NULL, 'dictionaries', '{"521/143": [-5]}', false);
INSERT INTO resource VALUES (522, 48, '{"-1": [{"value": 47}], "-5": [{"value": "holenderski"}], "139": [{"value": "holenderski"}], "140": [{"value": "dut"}], "143": [{"value": "holenderski"}], "144": [{"value": "5268"}]}', NULL, 'dictionaries', '{"522/143": [-5]}', false);
INSERT INTO resource VALUES (526, 48, '{"-1": [{"value": 47}], "-5": [{"value": "uniwersalny, universal"}], "139": [{"value": "jezyk__uniwersalny"}], "140": [{"value": "mul"}], "143": [{"value": "uniwersalny"}, {"value": "universal"}], "144": [{"value": "4"}]}', NULL, 'dictionaries', '{"526/143": [-5]}', false);
INSERT INTO resource VALUES (507, 48, '{"-1": [{"value": 47}], "-5": [{"value": "hiszpański"}], "139": [{"value": "jezyk__hiszpanski"}], "140": [{"value": "spa"}], "143": [{"value": "hiszpański"}], "144": [{"value": "1654"}]}', NULL, 'dictionaries', '{"507/143": [-5]}', false);
INSERT INTO resource VALUES (508, 48, '{"-1": [{"value": 47}], "-5": [{"value": "japoński"}], "139": [{"value": "japonski"}], "140": [{"value": "jpn"}], "143": [{"value": "japoński"}], "144": [{"value": "3602"}]}', NULL, 'dictionaries', '{"508/143": [-5]}', false);
INSERT INTO resource VALUES (509, 48, '{"-1": [{"value": 47}], "-5": [{"value": "kataloński"}], "139": [{"value": "jezyk_katalonski"}], "140": [{"value": "cat"}], "143": [{"value": "kataloński"}], "144": [{"value": "2990"}]}', NULL, 'dictionaries', '{"509/143": [-5]}', false);
INSERT INTO resource VALUES (512, 48, '{"-1": [{"value": 47}], "-5": [{"value": "portugalski"}], "139": [{"value": "jezyk_portugalski"}], "140": [{"value": "por"}], "143": [{"value": "portugalski"}], "144": [{"value": "3058"}]}', NULL, 'dictionaries', '{"512/143": [-5]}', false);
INSERT INTO resource VALUES (519, 48, '{"-1": [{"value": 47}], "-5": [{"value": "łaciński"}], "139": [{"value": "jezyk__lacinski"}], "140": [{"value": "lat"}], "143": [{"value": "łaciński"}], "144": [{"value": "1557"}]}', NULL, 'dictionaries', '{"519/143": [-5]}', false);
INSERT INTO resource VALUES (523, 48, '{"-1": [{"value": 47}], "-5": [{"value": "język nieokreślony, undefined language"}], "139": [{"value": "jezyk__nieokreslony"}], "140": [{"value": "und"}], "143": [{"value": "język nieokreślony"}, {"value": "undefined language"}], "144": [{"value": "3"}]}', NULL, 'dictionaries', '{"523/143": [-5]}', false);
INSERT INTO resource VALUES (524, 48, '{"-1": [{"value": 47}], "-5": [{"value": "serbsko-chorwacki"}], "139": [{"value": "serbsko_chorwacki"}], "140": [{"value": "[none]"}], "143": [{"value": "serbsko-chorwacki"}], "144": [{"value": "4308"}]}', NULL, 'dictionaries', '{"524/143": [-5]}', false);
INSERT INTO resource VALUES (525, 48, '{"-1": [{"value": 47}], "-5": [{"value": "szwedzki"}], "139": [{"value": "jezyk__szwedzki"}], "140": [{"value": "swe"}], "143": [{"value": "szwedzki"}], "144": [{"value": "5327"}]}', NULL, 'dictionaries', '{"525/143": [-5]}', false);
INSERT INTO resource VALUES (514, 48, '{"-1": [{"value": 47}], "-5": [{"value": "słowacki"}], "139": [{"value": "jezyk_slowacki"}], "140": [{"value": "slo"}], "143": [{"value": "słowacki"}], "144": [{"value": "1657"}]}', NULL, 'dictionaries', '{"514/143": [-5]}', false);
INSERT INTO resource VALUES (515, 48, '{"-1": [{"value": 47}], "-5": [{"value": "ukraiński"}], "139": [{"value": "jezyk_ukrainski"}], "140": [{"value": "ukr"}], "143": [{"value": "ukraiński"}], "144": [{"value": "1655"}]}', NULL, 'dictionaries', '{"515/143": [-5]}', false);
INSERT INTO resource VALUES (516, 48, '{"-1": [{"value": 47}], "-5": [{"value": "wietnamski"}], "139": [{"value": "jezyk_wietnamski"}], "140": [{"value": "vie"}], "143": [{"value": "wietnamski"}], "144": [{"value": "2988"}]}', NULL, 'dictionaries', '{"516/143": [-5]}', false);
INSERT INTO resource VALUES (517, 48, '{"-1": [{"value": 47}], "-5": [{"value": "węgierski"}], "139": [{"value": "wegierski"}], "140": [{"value": "hun"}], "143": [{"value": "węgierski"}], "144": [{"value": "3217"}]}', NULL, 'dictionaries', '{"517/143": [-5]}', false);
INSERT INTO resource VALUES (518, 48, '{"-1": [{"value": 47}], "-5": [{"value": "włoski"}], "139": [{"value": "jezyk__wloski"}], "140": [{"value": "ita"}], "143": [{"value": "włoski"}], "144": [{"value": "1554"}]}', NULL, 'dictionaries', '{"518/143": [-5]}', false);
INSERT INTO resource VALUES (502, 48, '{"-1": [{"value": 47}], "-5": [{"value": "polski, Polish"}], "139": [{"value": "jezyk__polski"}], "140": [{"value": "pol"}], "143": [{"value": "polski"}, {"value": "Polish"}], "144": [{"value": "5"}]}', NULL, 'dictionaries', '{"502/143": [-5]}', false);
INSERT INTO resource VALUES (503, 48, '{"-1": [{"value": 47}], "-5": [{"value": "angielski, English"}], "139": [{"value": "jezyk__angielski"}], "140": [{"value": "eng"}], "143": [{"value": "angielski"}, {"value": "English"}], "144": [{"value": "6"}]}', NULL, 'dictionaries', '{"503/143": [-5]}', false);
INSERT INTO resource VALUES (504, 48, '{"-1": [{"value": 47}], "-5": [{"value": "białoruski"}], "139": [{"value": "jezyka_bialoruski"}], "140": [{"value": "bel"}], "143": [{"value": "białoruski"}], "144": [{"value": "2884"}]}', NULL, 'dictionaries', '{"504/143": [-5]}', false);
INSERT INTO resource VALUES (505, 48, '{"-1": [{"value": 47}], "-5": [{"value": "czeski"}], "139": [{"value": "jezyk_czeski"}], "140": [{"value": "cze"}], "143": [{"value": "czeski"}], "144": [{"value": "1656"}]}', NULL, 'dictionaries', '{"505/143": [-5]}', false);
INSERT INTO resource VALUES (506, 48, '{"-1": [{"value": 47}], "-5": [{"value": "francuski"}], "139": [{"value": "jezyk__francuski"}], "140": [{"value": "fre"}], "143": [{"value": "francuski"}], "144": [{"value": "1555"}]}', NULL, 'dictionaries', '{"506/143": [-5]}', false);
INSERT INTO resource VALUES (510, 48, '{"-1": [{"value": 47}], "-5": [{"value": "litewski, Lithuanian"}], "139": [{"value": "jezyk__litewski"}], "140": [{"value": "lit"}], "143": [{"value": "litewski"}, {"value": "Lithuanian"}], "144": [{"value": "1645"}]}', NULL, 'dictionaries', '{"510/143": [-5]}', false);
INSERT INTO resource VALUES (511, 48, '{"-1": [{"value": 47}], "-5": [{"value": "niemiecki"}], "139": [{"value": "jezyk__niemiecki"}], "140": [{"value": "deu"}], "143": [{"value": "niemiecki"}], "144": [{"value": "1553"}]}', NULL, 'dictionaries', '{"511/143": [-5]}', false);
INSERT INTO resource VALUES (513, 48, '{"-1": [{"value": 47}], "-5": [{"value": "rosyjski"}], "139": [{"value": "jezyk__rosyjski"}], "140": [{"value": "rus"}], "143": [{"value": "rosyjski"}], "144": [{"value": "1556"}]}', NULL, 'dictionaries', '{"513/143": [-5]}', false);


--
-- Data for Name: resource_kind; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO resource_kind VALUES (49, '{"PL": "Licencja"}', NULL, 'dictionaries', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mNazwa | first}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 139, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": 143, "label": {"PL": ""}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (80, '{"PL": "Element UKD"}', NULL, 'dictionaries', '[{"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5}, {"id": 168, "label": {"PL": ""}}, {"id": 169, "label": {"PL": ""}}, {"id": 170, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (79, '{"PL": "Indeks UKD"}', NULL, 'dictionaries', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [80], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}}, {"id": -4}, {"id": 106, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (53, '{"PL": "Indeks formatów"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mOpis }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [52], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (60, '{"PL": "Indeks rodzajów skanerów"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | m106 }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [14], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (74, '{"PL": "Zestaw ikon"}', NULL, 'cms', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [75], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{ r | m150 }}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 150, "label": {"PL": ""}, "description": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (14, '{"PL": "Rodzaj skanera"}', NULL, 'dictionaries', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "RODZAJ SKANERA: {{ r | m105}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 105, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (20, '{"PL": "kolekcja_czasopisma"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [22], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 74, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (35, '{"PL": "kolekcja_nieopublikowanych_materialow"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [42], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (54, '{"PL": "Indeks wydziałów"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mOpis }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (47, '{"GB": "ksiazka", "PL": "ksiazka"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [47], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 136, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 110, "label": {"PL": ""}}, {"id": 135, "label": {"PL": ""}}, {"id": 137, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 138, "label": {"PL": ""}, "constraints": {"resourceKind": [1000000, 83, 80, 79, 78, 77, 76, 75, 74, 64, 63, 62, 61, 60, 59, 58, 57, 56, 55, 54, 53, 52, 51, 50, 49, 48, 47, 42, 41, 40, 39, 38, 37, 36, 35, 34, 33, 32, 31, 30, 29, 28, 27, 26, 25, 24, 23, 22, 21, 20, 19, 18, 17, 16, 15, 14, 13, 12, 10, 9, 8, 7, 6, 5, 4, 1, -1]}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (5, '{"PL": "repozytorium, które będzie skasowane"}', 4, 'books', '[{"id": -5, "constraints": {"displayStrategy": "{{r|mtytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [19], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "repozytorium, które będzie skasowane"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (6, '{"PL": "kolekcja"}', NULL, 'books', '[{"id": -5, "constraints": {"displayStrategy": "Kolekcja: {{r|mtytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [19, 23, 27, 30, 35, 36], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (75, '{"PL": "Ikona"}', NULL, 'cms', '[{"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "constraints": {"displayStrategy": "{{ r | m150 }}"}}, {"id": 152, "label": {"PL": ""}}, {"id": 150, "label": {"PL": "Tytuł"}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (30, '{"PL": "kolekcja_prac_dyplomowych"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [31, 33], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (26, '{"PL": "kolekcja_serii_wydawniczej"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [24, 25, 26], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (76, '{"PL": "Pasek z przyciskami"}', NULL, 'cms', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{ r | m150 }}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 150, "label": {"PL": ""}, "description": {"PL": ""}}, {"id": 151, "label": {"PL": ""}}, {"id": -4}, {"id": 149, "label": {"PL": ""}, "constraints": {"displayStrategy": "<generated-menu :first-group-of-items=\"[\n{% for link in r|mlink %}\n    {\n        label: ''{{ link|subNazwaLinku }}'',\n        iconName: ''{{ link | subIkonaLinku | first | mNazwaIkony }}'',\n        url: ''{{ link }}'',\n        highlightOnlyWhenPathnameEqualsURL: true\n    },\n{% endfor %}\n]\" :second-group-of-items=\"$user ? [\n    {\n        label: $user.username,\n        iconName: ''user-2'',\n        url: ''/user-details''\n    },\n    {\n        label: ''Panel Administracyjny'',\n        iconName: ''created'',\n        url: ''/admin''\n    },\n    {\n        label: ''Wyloguj'',\n        iconName: ''logout'',\n        url: ''/logout''\n    }\n] : [\n    {\n        label: ''Zaloguj'',\n        iconName: ''user-2'',\n        url: ''/login''\n    }\n]\" icons-size=\"1.5\">\n</generated-menu>"}, "placeholder": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (25, '{"PL": "kolekcja_fragmentow_ksiazek"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [7], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (24, '{"PL": "kolekcja_ksiazek"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [78, 1], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (77, '{"PL": "Strona statyczna"}', NULL, 'cms', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{ r | m150 }}"}}, {"id": 150, "label": {"PL": ""}}, {"id": -4}, {"id": 155, "label": {"PL": ""}}, {"id": 149, "label": {"PL": ""}, "constraints": {"displayStrategy": "{% extends \"redo/layout.twig\" %} {% set page_title = resource | mTytulStrony %} {% block content %}\n\n<h1>{{ r | mTytulStrony }}</h1>\n{{ r | mTrescStrony | raw }} {% endblock %}"}, "placeholder": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (57, '{"PL": "Rodzaj dostępu"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mNazwa }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (78, '{"GB": "numer_czasopisma_do_zeskanowania", "PL": "numer_czasopisma_do_zeskanowania"}', 4, 'books', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"GB": "", "PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": 3, "label": {"GB": "", "PL": ""}}, {"id": 120, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 119, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": -4}, {"id": 61, "label": {"PL": ""}}, {"id": 111, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 79, "label": {"PL": ""}}, {"id": 76, "label": {"PL": ""}}, {"id": 157, "label": {"PL": ""}}, {"id": 89, "label": {"PL": ""}}, {"id": 90, "label": {"PL": ""}}, {"id": 158, "label": {"PL": ""}}, {"id": 159, "label": {"PL": ""}}, {"id": 160, "label": {"PL": ""}}, {"id": 161, "label": {"PL": ""}}, {"id": 107, "label": {"PL": ""}}, {"id": 94, "label": {"PL": ""}}, {"id": 95, "label": {"PL": ""}}, {"id": 96, "label": {"PL": ""}}, {"id": 80, "label": {"PL": ""}}, {"id": 81, "label": {"PL": ""}}, {"id": 83, "label": {"PL": ""}}, {"id": 82, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (19, '{"PL": "kolekcja_czasopism"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [20, 21, 22], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (37, '{"PL": "poster"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "Poster"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 124, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (64, '{"PL": "Indeks skanerów"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mopis }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [63], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (17, '{"PL": "Indeks licencji"}', NULL, 'dictionaries', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mNazwa}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 142, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 140, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": 106, "label": {"PL": ""}}, {"id": 143, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (39, '{"PL": "program_konferencji"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "Program konferencji"}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (59, '{"PL": "Indeks trybów dokumentu"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | m106 }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [58], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (31, '{"PL": "kolekcja_prac_doktorskich"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [32], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (51, '{"PL": "Indeks klasyfikacjii PKT"}', NULL, 'dictionaries', '[{"id": -5}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [50], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (4, '{"PL": "Jednostka PK"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{r|m(20)}}"}}, {"id": 20, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}, "shownInBrief": true}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [4], "relatedResourceMetadataFilter": []}}, {"id": 21, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 22, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (52, '{"PL": "Format"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mNazwa }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 140, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (55, '{"PL": "Wydział"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mNazwa }}{% if r|mSkrot|first %} ({{ r | mSkrot }}){% endif %}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 139, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 140, "label": {"PL": ""}}, {"id": 145, "label": {"PL": ""}}, {"id": 146, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (61, '{"PL": "Tryb skanowania"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mnazwa }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (56, '{"PL": "Indeks rodzajów dostępu"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | m106 }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [57], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (13, '{"PL": "Indeks języków"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{r|mopis}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [48], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": "Indeks języków"}, "description": {"PL": "Indeks języków"}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (16, '{"PL": "Indeks jednostek"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{r|mopis}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [4], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (63, '{"PL": "Skaner"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | m143 }}"}}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (28, '{"PL": "kolekcja_publikacji_pokonferencyjnej"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [1, 7], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 74, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (40, '{"PL": "rozprawa_doktorska"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 130, "label": {"PL": ""}}, {"id": 133, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 132, "label": {"PL": ""}}, {"id": 129, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 134, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (50, '{"PL": "Klasyfikacja"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | m143 }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [50], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 139, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 140, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 144, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (10, '{"GB": "Grupa", "PL": "Grupa"}', NULL, 'users', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mgroup_name}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 73, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (12, '{"PL": "indeks rozdzielczości skanerów"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "ROZDZIELCZOŚĆ:{{r|mrozmiar_rozdzielczosci}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [12], "relatedResourceMetadataFilter": []}}, {"id": 103, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (62, '{"PL": "Indeks trybów skanowania"}', NULL, 'dictionaries', '[{"id": -5}, {"id": -1, "constraints": {"maxCount": 1, "resourceKind": [61], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (15, '{"PL": "Indeks rozdzielczosci"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{r|mopis}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [12], "relatedResourceMetadataFilter": []}}, {"id": 106, "label": {"PL": "rozdzielczość"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (23, '{"PL": "kolekcja_publikacji_ksiazkowych"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [24, 25, 26], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (21, '{"PL": "kolekcja_artykulow"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [9], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (27, '{"PL": "kolekcja_materialow_konferencyjnych"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [28, 29], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (29, '{"PL": "kolekcja_publikacji_konferencyjnych"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [1, 7, 9, 38, 39], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (18, '{"PL": "repozytorium"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTitle}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [6], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "Repozytorium"}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (22, '{"PL": "kolekcja_numerow_czasopisma"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [8, 9, 22], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 120, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 74, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (36, '{"PL": "kolekcja_materialow_informacyjnych"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [20, 26], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (34, '{"PL": "kolekcja_prac_magisterskich_jednostki"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (33, '{"PL": "kolekcja_prac_magisterskich"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [34], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (32, '{"PL": "kolekcja_prac_doktorskich_jednostki"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (38, '{"PL": "prezentacja"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "Prezentacja"}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 124, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 110, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (42, '{"PL": "material_nieopublikowany"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 124, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": 129, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (58, '{"PL": "Tryb dokumentu"}', NULL, 'dictionaries', '[{"id": -5, "constraints": {"displayStrategy": "{{ r | mNazwa | first | split(''/'')[1] | trim }}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}}, {"id": 139, "label": {"PL": ""}}, {"id": 144, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (9, '{"PL": "artykul"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 110, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 111, "label": {"PL": ""}}, {"id": 129, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 114, "label": {"PL": ""}}, {"id": 122, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 119, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (7, '{"PL": "fragment_ksiazki"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": "Fragment książki"}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 110, "label": {"PL": ""}}, {"id": 129, "label": {"PL": ""}}, {"id": 135, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 113, "label": {"PL": ""}}, {"id": 114, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 118, "label": {"PL": ""}}, {"id": 119, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (8, '{"PL": "numer_czasopisma"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}, "description": {"PL": ""}, "placeholder": {"PL": ""}}, {"id": 120, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 119, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (41, '{"PL": "praca_magisterska"}', NULL, 'books', '[{"id": -5, "label": {"PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 130, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 124, "label": {"PL": ""}}, {"id": 132, "label": {"PL": ""}}, {"id": 129, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 115, "label": {"PL": ""}}, {"id": 126, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 128, "label": {"PL": ""}}, {"id": -4}, {"id": 80, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (1000000, '{"EN": "Rodzaj_do_testowania_automatycznego", "PL": "Rodzaj_do_testowania_automatycznego"}', 1000000, 'books', '[{"id": -5}, {"id": -1, "label": {"EN": "", "PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 1000001, "label": {"EN": "", "PL": ""}}, {"id": 1000000, "label": {"EN": "", "PL": ""}}, {"id": 1000002, "label": {"EN": "", "PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (83, '{"GB": "asd", "PL": "asd"}', NULL, 'books', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}}, {"id": 197, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (1, '{"GB": "ksiazka_do_zeskanowania", "PL": "ksiazka_do_zeskanowania"}', 4, 'books', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "Projekt EDT-{{r|mTytul}}"}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 3, "label": {"PL": ""}}, {"id": 61, "label": {"PL": ""}}, {"id": 4, "label": {"PL": ""}}, {"id": 63, "label": {"PL": ""}}, {"id": 5, "label": {"PL": ""}}, {"id": 64, "label": {"PL": ""}}, {"id": 65, "label": {"PL": ""}}, {"id": 135, "label": {"PL": ""}}, {"id": 72, "label": {"PL": ""}}, {"id": 53, "label": {"PL": ""}}, {"id": 116, "label": {"PL": ""}}, {"id": 125, "label": {"PL": ""}}, {"id": 50, "label": {"PL": ""}}, {"id": 88, "label": {"PL": ""}}, {"id": 97, "label": {"PL": ""}}, {"id": 123, "label": {"PL": ""}}, {"id": 117, "label": {"PL": ""}}, {"id": 99, "label": {"PL": ""}}, {"id": 92, "label": {"PL": ""}}, {"id": 79, "label": {"PL": ""}}, {"id": 76, "label": {"PL": ""}, "shownInBrief": true}, {"id": 156, "label": {"PL": ""}}, {"id": -4}, {"id": 173, "label": {"PL": ""}}, {"id": 157, "label": {"PL": ""}}, {"id": 89, "label": {"PL": ""}}, {"id": 90, "label": {"PL": ""}}, {"id": 158, "label": {"PL": ""}}, {"id": 159, "label": {"PL": ""}}, {"id": 160, "label": {"PL": ""}}, {"id": 161, "label": {"PL": ""}}, {"id": 66, "label": {"PL": ""}}, {"id": 107, "label": {"PL": ""}}, {"id": 94, "label": {"PL": ""}}, {"id": 95, "label": {"PL": ""}}, {"id": 96, "label": {"PL": ""}}, {"id": 181, "label": {"PL": ""}}, {"id": 86, "label": {"PL": ""}}, {"id": 80, "label": {"PL": ""}}, {"id": 81, "label": {"PL": ""}}, {"id": 83, "label": {"PL": ""}}, {"id": 82, "label": {"PL": ""}}, {"id": 84, "label": {"PL": ""}}, {"id": 85, "label": {"PL": ""}}, {"id": 172, "label": {"PL": ""}}, {"id": 70, "label": {"PL": ""}}, {"id": 183, "label": {"PL": ""}}, {"id": 136, "label": {"PL": ""}}, {"id": 127, "label": {"PL": ""}}, {"id": 206, "label": {"GB": "", "PL": ""}}, {"id": 209, "label": {"GB": "", "PL": ""}}]');
INSERT INTO resource_kind VALUES (-1, '{"GB": "user", "PL": "user"}', NULL, 'users', '[{"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "{{r|m(26)}} {{r|m(27)}} ({{r|mUsername}})"}}, {"id": -2, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 26, "label": {"PL": ""}}, {"id": 27, "label": {"PL": ""}}, {"id": 28, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": 29, "label": {"PL": ""}}, {"id": 30, "label": {"PL": ""}}, {"id": 31, "label": {"PL": ""}}, {"id": 32, "label": {"PL": ""}}, {"id": 33, "label": {"PL": ""}}, {"id": 34, "label": {"PL": ""}}, {"id": 35, "label": {"PL": ""}}, {"id": 36, "label": {"PL": ""}}, {"id": 78, "label": {"PL": ""}}, {"id": -3, "label": {"PL": ""}, "constraints": {"relatedResourceMetadataFilter": []}}, {"id": -4}, {"id": 246, "label": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}]');
INSERT INTO resource_kind VALUES (92, '{"GB": "750 test", "PL": "750 test"}', NULL, 'books', '[{"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5}, {"id": 138, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (89, '{"GB": "TEST_RODZAJ", "PL": "TEST_RODZAJ"}', 8, 'books', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [89], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}}, {"id": 212, "label": {"GB": "", "PL": ""}, "shownInBrief": true}, {"id": -4}, {"id": 220, "label": {"GB": "", "PL": ""}}, {"id": 218, "label": {"GB": "", "PL": ""}}, {"id": 217, "label": {"GB": "", "PL": ""}}, {"id": 216, "label": {"GB": "", "PL": ""}}, {"id": 215, "label": {"GB": "", "PL": ""}, "groupId": "basic", "copyToChildResource": true}, {"id": 214, "label": {"GB": "", "PL": ""}}, {"id": 211, "label": {"GB": "", "PL": ""}}, {"id": 219, "label": {"GB": "", "PL": ""}}]');
INSERT INTO resource_kind VALUES (93, '{"GB": "test", "PL": "test"}', 4, 'books', '[{"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5, "label": {"PL": ""}, "constraints": {"displayStrategy": "#{{ r.id }} {{r|mtytul}}<br>"}}, {"id": 209, "label": {"GB": "", "PL": ""}}, {"id": 3, "label": {"PL": ""}}, {"id": -4}, {"id": 223, "label": {"GB": "", "PL": ""}, "constraints": {"minMaxValue": {"min": 1000}}}]');
INSERT INTO resource_kind VALUES (109, '{"GB": "manually aded user ", "PL": "użytkownik dodany ręcznie"}', NULL, 'users', '[{"id": -1, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": -5}, {"id": 26, "label": {"PL": ""}}, {"id": 27, "label": {"PL": ""}}, {"id": -4}]');
INSERT INTO resource_kind VALUES (48, '{"GB": "Language", "PL": "Język"}', NULL, 'dictionaries', '[{"id": -5, "label": {"GB": "", "PL": ""}, "constraints": {"displayStrategy": "{{r|mnazwa}}"}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": -1, "label": {"PL": ""}, "constraints": {"maxCount": 1, "resourceKind": [], "relatedResourceMetadataFilter": []}}, {"id": 143, "label": {"PL": ""}, "shownInBrief": true}, {"id": 104, "label": {"PL": ""}}, {"id": 106, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": 144, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": 139, "label": {"GB": "", "PL": ""}, "description": {"GB": "", "PL": ""}, "placeholder": {"GB": "", "PL": ""}}, {"id": -4}, {"id": 140, "label": {"PL": ""}}]');
INSERT INTO resource_kind VALUES (125, '{"GB": "testResourceKind", "PL": "testResourceKind"}', NULL, 'books', '[{"id": 53}, {"id": 156}, {"id": 125}, {"id": 4}, {"id": 63}, {"id": 175}, {"id": 62}, {"id": 99}, {"id": 3}, {"id": 61}, {"id": 64}, {"id": 65}, {"id": 5}, {"id": 172}, {"id": 66}, {"id": 129}, {"id": 70}, {"id": 86}, {"id": 181}, {"id": 135}, {"id": 72}, {"id": 79}, {"id": 76}, {"id": 117}, {"id": 173}, {"id": -1}, {"id": -4}, {"id": -5}]');


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: workflow; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO workflow VALUES (1000000, '{"GB": "Proces_do_testowania_automatycznego", "PL": "Proces_do_testowania_automatycznego"}', '[{"id": "l670ocqie", "label": {"EN": "A", "GB": "A", "PL": "A"}, "pluginsConfig": [{"name": "repekaOcr", "config": []}], "lockedMetadataIds": [1000000], "assigneeMetadataIds": [], "requiredMetadataIds": [1000001], "autoAssignMetadataIds": []}, {"id": "yilhcutph", "label": {"EN": "KONIEC", "GB": "KONIEC", "PL": "KONIEC"}, "pluginsConfig": [{"name": "repekaOcr", "config": []}, {"name": "repekaMetadataValueSetter", "config": []}], "lockedMetadataIds": [1000000], "assigneeMetadataIds": [], "requiredMetadataIds": [1000001], "autoAssignMetadataIds": [-4]}]', '[{"id": "1d26bf77-3e85-4678-9080-caa427ffec97", "tos": ["yilhcutph"], "froms": ["l670ocqie"], "label": {"EN": "przejście", "GB": "przejście", "PL": "przejście"}}]', '{"l670ocqie":{"x":338,"y":278.3999938964844},"yilhcutph":{"x":434.9812469482422,"y":227.24999237060547}}', 'data:image/png;base64,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', 'books');
INSERT INTO workflow VALUES (4, '{"GB": "EDT książka", "PL": "EDT książka"}', '[{"id": "cbbcjpned", "label": {"GB": "nowy rekord", "PL": "nowy rekord"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "data_utworzenia_rekordu", "metadataValue": "{{ \"now\"|date(\"Y-m-d\") }}"}}], "lockedMetadataIds": {"3": 74, "6": 79, "7": 81, "8": 82, "9": 83, "10": 84, "11": 85, "12": 88, "13": 89, "14": 90, "15": 91, "16": 92, "17": 94, "18": 95, "19": 96, "20": 97, "22": 101, "23": 102, "24": 107, "25": 108, "26": 109}, "assigneeMetadataIds": [], "requiredMetadataIds": [76], "autoAssignMetadataIds": [80, 209]}, {"id": "m0fnphvq8", "label": {"GB": "przed przydzieleniem skanisty", "PL": "przed przydzieleniem skanisty"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "osoba_przydzielajaca_zadanie_skanowania", "metadataValue": "25"}}], "lockedMetadataIds": {"3": 74, "5": 76, "7": 79, "8": 81, "9": 82, "10": 83, "11": 84, "12": 85, "13": 88, "14": 89, "15": 90, "16": 91, "17": 92, "18": 94, "19": 95, "20": 96, "21": 97, "23": 101, "24": 102, "25": 107, "26": 108, "27": 109}, "assigneeMetadataIds": [80, 209], "requiredMetadataIds": [3, 5, 53, 64], "autoAssignMetadataIds": []}, {"id": "9fjz62fsq", "label": {"GB": "przydzielony do skanowania", "PL": "przydzielony do skanowania"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "Osoba przydzielająca zadanie skanowania ", "metadataValue": "3"}}], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 82, "29": 84, "30": 85, "31": 86, "33": 88, "34": 89, "35": 90, "36": 91, "37": 92, "38": 94, "39": 95, "40": 96, "41": 97, "43": 101, "44": 102, "45": 107, "46": 108, "47": 109, "48": 209}, "assigneeMetadataIds": [81], "requiredMetadataIds": [83], "autoAssignMetadataIds": []}, {"id": "35kw1j2lm", "label": {"GB": "w skanowaniu", "PL": "w skanowaniu"}, "pluginsConfig": [], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 82, "30": 84, "31": 85, "32": 86, "34": 88, "35": 89, "36": 90, "37": 91, "38": 92, "39": 94, "40": 95, "41": 96, "42": 97, "44": 101, "45": 102, "46": 107, "47": 108, "48": 109, "49": 209}, "assigneeMetadataIds": [83], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "5qvpl5nm2", "label": {"GB": "po skanowaniu", "PL": "po skanowaniu"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "osoba_przydzielajaca_zadanie_obrobki_graficznej", "metadataValue": "3"}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": "data_zakonczenia_skanowania", "metadataValue": "{{ \"now\"|date(\"Y-m-d\") }}"}}], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "6": 50, "7": 52, "8": 53, "9": 61, "10": 62, "11": 63, "12": 64, "13": 65, "14": 66, "15": 67, "18": 70, "20": 72, "21": 74, "23": 76, "25": 79, "26": 80, "27": 81, "28": 82, "29": 84, "30": 85, "31": 86, "33": 88, "34": 89, "35": 90, "36": 91, "37": 92, "38": 94, "39": 95, "40": 96, "41": 97, "43": 101, "44": 102, "45": 108, "46": 109, "47": 209}, "assigneeMetadataIds": [83], "requiredMetadataIds": {"1": 107}, "autoAssignMetadataIds": []}, {"id": "6hlsbun59", "label": {"GB": "przydzielony do obróbki graficznej", "PL": "przydzielony do obróbki graficznej"}, "pluginsConfig": [], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 83, "30": 85, "31": 86, "33": 88, "34": 89, "35": 90, "36": 91, "37": 92, "38": 94, "39": 95, "40": 96, "41": 97, "43": 101, "44": 102, "45": 107, "46": 108, "47": 109, "48": 209}, "assigneeMetadataIds": [82], "requiredMetadataIds": [84], "autoAssignMetadataIds": []}, {"id": "s1jd4qvkz", "label": {"GB": "w obróbce", "PL": "w obróbce"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "uprawnionych_korektorow_grupa", "metadataValue": "68"}}], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 82, "30": 83, "31": 85, "32": 86, "34": 88, "35": 89, "36": 90, "37": 91, "38": 92, "39": 94, "40": 95, "41": 96, "42": 97, "44": 101, "45": 102, "46": 107, "47": 108, "48": 109, "49": 209}, "assigneeMetadataIds": [84], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "hfhqv7ktd", "label": {"GB": "przed korektą", "PL": "przed korektą"}, "pluginsConfig": [], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 82, "30": 83, "31": 85, "32": 86, "34": 88, "35": 89, "36": 90, "37": 91, "38": 92, "39": 94, "40": 95, "41": 96, "42": 101, "43": 102, "44": 107, "45": 109, "46": 209}, "assigneeMetadataIds": [84], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "7yrodqg56", "label": {"GB": "w korekcie", "PL": "w korekcie"}, "pluginsConfig": [], "lockedMetadataIds": {"3": 62, "5": 74, "7": 76, "9": 79, "10": 80, "11": 81, "12": 82, "13": 83, "14": 88, "15": 89, "16": 90, "17": 91, "18": 92, "19": 94, "20": 95, "21": 96, "22": 101, "23": 102, "24": 107, "25": 209}, "assigneeMetadataIds": [84, 109], "requiredMetadataIds": [3, 5, 53, 64], "autoAssignMetadataIds": [85]}, {"id": "gz4jfhheb", "label": {"GB": "opublikowany", "PL": "opublikowany"}, "pluginsConfig": [], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 82, "30": 83, "31": 86, "33": 88, "34": 89, "35": 90, "36": 91, "37": 92, "38": 94, "39": 95, "40": 96, "41": 99, "42": 101, "43": 102, "44": 107, "45": 109, "46": 209}, "assigneeMetadataIds": [84, 85], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "hw4h8068d", "label": {"GB": "zakończony", "PL": "zakończony"}, "pluginsConfig": [], "lockedMetadataIds": {"0": 3, "1": 4, "2": 5, "3": 6, "7": 50, "8": 52, "9": 53, "10": 61, "11": 62, "12": 63, "13": 64, "14": 65, "15": 66, "16": 67, "19": 70, "21": 72, "22": 74, "24": 76, "26": 79, "27": 80, "28": 81, "29": 82, "30": 83, "31": 85, "32": 86, "34": 88, "35": 89, "36": 90, "37": 91, "38": 92, "39": 94, "40": 95, "41": 96, "42": 99, "43": 101, "44": 102, "45": 107, "46": 109, "47": 209}, "assigneeMetadataIds": [84], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "1u23v57m5", "label": {"GB": "ponownie w skanowaniu", "PL": "ponownie w skanowaniu"}, "pluginsConfig": [], "lockedMetadataIds": [80, 81, 82, 83, 85, 209], "assigneeMetadataIds": [84], "requiredMetadataIds": [], "autoAssignMetadataIds": []}]', '[{"id": "a3c4b171-d3e4-4097-b168-e08d319112a6", "tos": ["7yrodqg56"], "froms": ["hfhqv7ktd"], "label": {"GB": "rozpocznij korektę", "PL": "rozpocznij korektę"}}, {"id": "962c96a5-5d20-4b52-a54c-9f24d898cd8e", "tos": ["m0fnphvq8"], "froms": ["cbbcjpned"], "label": {"GB": "wyślij do skanowania", "PL": "wyślij do skanowania"}}, {"id": "ae5e72c1-de20-4a1f-9df0-ca051f4d235a", "tos": ["9fjz62fsq"], "froms": ["m0fnphvq8"], "label": {"GB": "przydziel do skanowania", "PL": "przydziel do skanowania"}}, {"id": "c9060430-aa5f-45d8-b602-9424900f1714", "tos": ["35kw1j2lm"], "froms": ["9fjz62fsq"], "label": {"GB": "rozpocznij skanowanie", "PL": "rozpocznij skanowanie"}}, {"id": "fcc7d772-651a-41b7-ba7a-4b0053b98517", "tos": ["5qvpl5nm2"], "froms": ["35kw1j2lm"], "label": {"GB": "wyślij do obróbki graficznej", "PL": "wyślij do obróbki graficznej"}}, {"id": "7909cf84-a1d5-4da6-a669-285c931b5399", "tos": ["6hlsbun59"], "froms": ["5qvpl5nm2"], "label": {"GB": "przydziel grafika", "PL": "przydziel grafika"}}, {"id": "24388dd6-bbdc-4b53-b0be-fc1d47f60c59", "tos": ["s1jd4qvkz"], "froms": ["6hlsbun59"], "label": {"GB": "rozpocznij obróbkę", "PL": "rozpocznij obróbkę"}}, {"id": "ba6e1c20-a1b1-45b4-a897-12692c5f1c98", "tos": ["gz4jfhheb"], "froms": ["7yrodqg56"], "label": {"GB": "publikuj rekord", "PL": "publikuj rekord"}}, {"id": "56bdc5ef-622f-4468-a52e-3bb035d64a90", "tos": ["hw4h8068d"], "froms": ["gz4jfhheb"], "label": {"GB": "zablokuj edycję", "PL": "zablokuj edycję"}}, {"id": "1a864e39-cfe7-4a1b-b4cb-8757b8e2779d", "tos": ["hfhqv7ktd"], "froms": ["s1jd4qvkz"], "label": {"GB": "wyślij do korekty", "PL": "wyślij do korekty"}}, {"id": "fa22c059-ebf9-437c-b328-556da00cbd45", "tos": ["1u23v57m5"], "froms": ["s1jd4qvkz"], "label": {"GB": "wyslij do ponownego skanowania", "PL": "wyslij do ponownego skanowania"}}, {"id": "627b12c7-c608-463f-ac17-4fa9e0355ade", "tos": ["s1jd4qvkz"], "froms": ["1u23v57m5"], "label": {"GB": "wyślij do ponownej obróbki", "PL": "wyślij do ponownej obróbki"}}]', '{"cbbcjpned":{"x":524.2480158995517,"y":-116.55886019819545},"m0fnphvq8":{"x":925.7256620142476,"y":-112.8848903755204},"9fjz62fsq":{"x":1371.3904989742275,"y":-58.005807935673396},"35kw1j2lm":{"x":525.9034865571222,"y":-26.988509340390856},"5qvpl5nm2":{"x":818.5167427185605,"y":87.76703934962684},"6hlsbun59":{"x":1110.984836497523,"y":110.88875140266065},"s1jd4qvkz":{"x":455.29818392407,"y":343.8702638719423},"hfhqv7ktd":{"x":861.3989613390926,"y":290.39979261019954},"7yrodqg56":{"x":1073.9015882306446,"y":349.87786640065207},"gz4jfhheb":{"x":1214.2150470614508,"y":181.90858487614219},"hw4h8068d":{"x":1460.1444382530776,"y":345.24933982901496},"1u23v57m5":{"x":394.7457838317423,"y":25.274562799812536}}', 'data:image/png;base64,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', 'books');
INSERT INTO workflow VALUES (8, '{"GB": "PROCES_TEST", "PL": "PROCES_TEST"}', '[{"id": "o82xow2nd", "label": {"GB": "START", "PL": "START"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [212], "autoAssignMetadataIds": [-4]}, {"id": "ti189nj1g", "label": {"GB": "KONIEC", "PL": "KONIEC"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [211], "autoAssignMetadataIds": [-4]}]', '[{"id": "13beae8f-5374-4f8c-a2fd-a0b8f65a0544", "tos": ["ti189nj1g"], "froms": ["o82xow2nd"], "label": {"GB": "P", "PL": "P"}}]', '{"o82xow2nd":{"x":272,"y":230.59999084472656},"ti189nj1g":{"x":334.5999984741211,"y":196.4999885559082}}', 'data:image/png;base64,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', 'books');
INSERT INTO workflow VALUES (10, '{"GB": "test", "PL": "test"}', '[{"id": "h9qvkg3tv", "label": {"GB": "test", "PL": "test"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [], "autoAssignMetadataIds": []}]', '[]', '{"h9qvkg3tv":{"x":323,"y":142}}', 'data:image/png;base64,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', 'books');


--
-- Name: audit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('audit_id_seq', 23202, true);


--
-- Name: endpoint_usage_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('endpoint_usage_log_id_seq', 1, false);


--
-- Name: metadata_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('metadata_id_seq', 277, true);


--
-- Name: resource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('resource_id_seq', 8526, true);


--
-- Name: resource_kind_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('resource_kind_id_seq', 125, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('user_id_seq', 22, true);


--
-- Name: workflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('workflow_id_seq', 10, true);


--
-- Name: audit audit_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT audit_pkey PRIMARY KEY (id);


--
-- Name: endpoint_usage_log endpoint_usage_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY endpoint_usage_log
    ADD CONSTRAINT endpoint_usage_log_pkey PRIMARY KEY (id);


--
-- Name: language language_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY language
    ADD CONSTRAINT language_pkey PRIMARY KEY (code);


--
-- Name: metadata metadata_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY metadata
    ADD CONSTRAINT metadata_pkey PRIMARY KEY (id);


--
-- Name: migration_versions migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY migration_versions
    ADD CONSTRAINT migration_versions_pkey PRIMARY KEY (version);


--
-- Name: resource_kind resource_kind_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource_kind
    ADD CONSTRAINT resource_kind_pkey PRIMARY KEY (id);


--
-- Name: resource resource_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource
    ADD CONSTRAINT resource_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: workflow workflow_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY workflow
    ADD CONSTRAINT workflow_pkey PRIMARY KEY (id);


--
-- Name: audit_entry_type_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_entry_type_idx ON audit USING btree (commandname);


--
-- Name: idx_34e41c632c7c2cba; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_34e41c632c7c2cba ON resource_kind USING btree (workflow_id);


--
-- Name: idx_3b76444189329d25; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_3b76444189329d25 ON endpoint_usage_log USING btree (resource_id);


--
-- Name: idx_4f1434146967df41; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_4f1434146967df41 ON metadata USING btree (base_id);


--
-- Name: idx_4f143414727aca70; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_4f143414727aca70 ON metadata USING btree (parent_id);


--
-- Name: idx_9218ff79a76ed395; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_9218ff79a76ed395 ON audit USING btree (user_id);


--
-- Name: idx_bc91f41630602ca9; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bc91f41630602ca9 ON resource USING btree (kind_id);


--
-- Name: uniq_8d93d6496ff8bf36; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX uniq_8d93d6496ff8bf36 ON "user" USING btree (user_data_id);


--
-- Name: resource_kind fk_34e41c632c7c2cba; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource_kind
    ADD CONSTRAINT fk_34e41c632c7c2cba FOREIGN KEY (workflow_id) REFERENCES workflow(id);


--
-- Name: endpoint_usage_log fk_3b76444189329d25; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY endpoint_usage_log
    ADD CONSTRAINT fk_3b76444189329d25 FOREIGN KEY (resource_id) REFERENCES resource(id);


--
-- Name: metadata fk_4f1434146967df41; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY metadata
    ADD CONSTRAINT fk_4f1434146967df41 FOREIGN KEY (base_id) REFERENCES metadata(id);


--
-- Name: metadata fk_4f143414727aca70; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY metadata
    ADD CONSTRAINT fk_4f143414727aca70 FOREIGN KEY (parent_id) REFERENCES metadata(id);


--
-- Name: user fk_8d93d6496ff8bf36; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT fk_8d93d6496ff8bf36 FOREIGN KEY (user_data_id) REFERENCES resource(id) ON DELETE CASCADE;


--
-- Name: audit fk_9218ff79a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT fk_9218ff79a76ed395 FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE SET NULL;


--
-- Name: resource fk_bc91f41630602ca9; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource
    ADD CONSTRAINT fk_bc91f41630602ca9 FOREIGN KEY (kind_id) REFERENCES resource_kind(id);


--
-- PostgreSQL database dump complete
--

