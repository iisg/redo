--
-- PostgreSQL database dump
--

-- Dumped from database version 10.1
-- Dumped by pg_dump version 10.1

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
    copy_to_child_resource boolean DEFAULT false NOT NULL
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
    resource_class character varying(64) DEFAULT NULL::character varying NOT NULL
);


ALTER TABLE resource OWNER TO postgres;

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
    display_strategies jsonb NOT NULL,
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
-- Name: role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE role (
    name jsonb NOT NULL,
    id integer NOT NULL
);


ALTER TABLE role OWNER TO postgres;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE role_id_seq OWNER TO postgres;

--
-- Name: user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE "user" (
    id integer NOT NULL,
    password character varying(64),
    is_active boolean NOT NULL,
    user_data_id integer NOT NULL
);


ALTER TABLE "user" OWNER TO postgres;

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
-- Name: user_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE user_role (
    user_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE user_role OWNER TO postgres;

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

INSERT INTO audit VALUES (1, NULL, 'resource_create', '{"resource": {"id": 1, "kindId": -1, "places": [], "contents": [], "resourceClass": "users"}}', true, '2018-06-18 13:13:49+02');
INSERT INTO audit VALUES (2, NULL, 'resource_create', '{"resource": {"id": 2, "kindId": -1, "places": [], "contents": [], "resourceClass": "users"}}', true, '2018-06-18 13:13:50+02');
INSERT INTO audit VALUES (3, NULL, 'resource_create', '{"resource": {"id": 3, "kindId": -1, "places": [], "contents": [], "resourceClass": "users"}}', true, '2018-06-18 13:13:51+02');
INSERT INTO audit VALUES (4, NULL, 'resource_create', '{"resource": {"id": 4, "kindId": -1, "places": [], "contents": [], "resourceClass": "users"}}', true, '2018-06-18 13:13:51+02');
INSERT INTO audit VALUES (5, NULL, 'resource_create', '{"resource": {"id": 5, "kindId": 7, "places": [], "contents": {"-2": [{"value": "Administratorzy"}]}, "resourceClass": "users"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (6, NULL, 'resource_create', '{"resource": {"id": 6, "kindId": 7, "places": [], "contents": {"-2": [{"value": "Skaniści"}]}, "resourceClass": "users"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (7, NULL, 'resource_update_contents', '{"resource": {"id": 1, "kindId": -1, "places": [], "contents": {"-2": [{"value": "admin"}]}, "resourceClass": "users"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (8, NULL, 'resource_update_contents', '{"resource": {"id": 2, "kindId": -1, "places": [], "contents": {"-2": [{"value": "budynek"}]}, "resourceClass": "users"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (9, NULL, 'resource_update_contents', '{"resource": {"id": 4, "kindId": -1, "places": [], "contents": {"-2": [{"value": "skaner"}]}, "resourceClass": "users"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (10, NULL, 'resource_create', '{"resource": {"id": 7, "kindId": 5, "places": [], "contents": {"27": [{"value": "Akademia Górniczo Hutnicza"}], "28": [{"value": "AGH"}]}, "resourceClass": "dictionaries"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (11, NULL, 'resource_create', '{"resource": {"id": 8, "kindId": 5, "places": [], "contents": {"27": [{"value": "Politechnika Krakowska"}], "28": [{"value": "PK"}]}, "resourceClass": "dictionaries"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (12, NULL, 'resource_create', '{"resource": {"id": 9, "kindId": 4, "places": [], "contents": {"27": [{"value": "Informatyki, Elektroniki i Telekomunikacji"}], "28": [{"value": "IET"}], "29": [{"value": 7}]}, "resourceClass": "dictionaries"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (13, NULL, 'resource_create', '{"resource": {"id": 10, "kindId": 4, "places": [], "contents": {"27": [{"value": "Elektroniki, Automatyki i Inżynierii Biomedycznej"}], "28": [{"value": "EAIB"}], "29": [{"value": 7}]}, "resourceClass": "dictionaries"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (14, NULL, 'resource_create', '{"resource": {"id": 11, "kindId": 6, "places": [], "contents": {"27": [{"value": "Wydawnictwo Zakładu Narodowego im. Ossolińskich"}]}, "resourceClass": "dictionaries"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (15, NULL, 'resource_create', '{"resource": {"id": 12, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "PHP i MySQL"}], "13": [{"value": "Błędy młodości..."}], "16": [{"value": 404}], "21": [{"value": 4}], "22": [{"value": 2}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (16, NULL, 'resource_create', '{"resource": {"id": 13, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "PHP - to można leczyć!"}], "13": [{"value": "Poradnik dla cierpiących na zwyrodnienie interpretera."}], "15": [{"value": true}], "16": [{"value": 1337}], "18": [{"value": 12}], "21": [{"value": 4}], "22": [{"value": 2}], "25": [{"value": 12}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (17, NULL, 'resource_create', '{"resource": {"id": 14, "kindId": 2, "places": [], "contents": {"12": [{"value": "Python dla opornych"}], "23": [{"value": 9}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:52+02');
INSERT INTO audit VALUES (18, NULL, 'resource_create', '{"resource": {"id": 15, "kindId": 3, "places": [], "contents": {"20": [{"value": "E-booki"}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (19, NULL, 'resource_create', '{"resource": {"id": 16, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"-1": [{"value": 15}], "12": [{"value": "\"Mogliśmy użyć Webpacka\" i inne spóźnione mądrości"}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (20, NULL, 'resource_create', '{"resource": {"id": 17, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"-1": [{"value": 15}], "12": [{"value": "Pair programming: jak równocześnie pisać na jednej klawiaturze w dwie osoby"}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (21, NULL, 'resource_transition', '{"resource": {"id": 12, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "PHP i MySQL"}], "13": [{"value": "Błędy młodości..."}], "16": [{"value": 404}], "21": [{"value": 4}], "22": [{"value": 2}]}, "resourceClass": "books"}, "transitionId": "e7d756ed-d6b3-4f2f-9517-679311e88b17", "transitionLabel": {"EN": "Attach metrics", "PL": "Dołącz metryczkę"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (22, NULL, 'resource_transition', '{"resource": {"id": 12, "kindId": 1, "places": ["lb1ovdqcy"], "contents": {"12": [{"value": "PHP i MySQL"}], "13": [{"value": "Błędy młodości..."}], "16": [{"value": 404}], "21": [{"value": 4}], "22": [{"value": 2}]}, "resourceClass": "books"}, "transitionId": "d3f73249-d10f-4d4b-8b63-be60b4c02081", "transitionLabel": {"EN": "Scan", "PL": "Skanuj"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (23, NULL, 'resource_create', '{"resource": {"id": 18, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Realigned assymetric database"}], "13": [{"value": "Hatter. ''You MUST remember,'' remarked the King, ''unless it was looking about for a minute or two she stood watching them, and then I''ll tell him--it was for bringing the cook till his eyes very wide."}], "16": [{"value": 437}], "21": [{"value": 1}], "22": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (24, NULL, 'resource_create', '{"resource": {"id": 19, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Team-oriented multi-state product"}], "13": [{"value": "Hatter, who turned pale and fidgeted. ''Give your evidence,'' said the Mock Turtle said: ''advance twice, set to partners--'' ''--change lobsters, and retire in same order,'' continued the Hatter, ''you."}], "16": [{"value": 517}], "21": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (25, NULL, 'resource_create', '{"resource": {"id": 20, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Phased modular moderator"}], "13": [{"value": "Alice. ''I''ve read that in the air, mixed up with the next moment she felt that she was appealed to by the White Rabbit, ''but it seems to grin, How neatly spread his claws, And welcome little fishes."}], "16": [{"value": 851}], "21": [{"value": 1}], "22": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (26, NULL, 'resource_create', '{"resource": {"id": 21, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Switchable solution-oriented flexibility"}], "13": [{"value": "However, when they saw Alice coming. ''There''s PLENTY of room!'' said Alice desperately: ''he''s perfectly idiotic!'' And she began fancying the sort of thing that would happen: ''\"Miss Alice! Come here."}], "16": [{"value": 202}], "21": [{"value": 2}], "22": [{"value": 2}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (27, NULL, 'resource_create', '{"resource": {"id": 22, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Front-line neutral data-warehouse"}], "13": [{"value": "Beautiful, beauti--FUL SOUP!'' ''Chorus again!'' cried the Gryphon, with a soldier on each side, and opened their eyes and mouths so VERY nearly at the White Rabbit; ''in fact, there''s nothing written."}], "16": [{"value": 204}], "21": [{"value": 1}], "22": [{"value": 2}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (28, NULL, 'resource_create', '{"resource": {"id": 23, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Focused systemic opensystem"}], "13": [{"value": "As there seemed to be Involved in this affair, He trusts to you never to lose YOUR temper!'' ''Hold your tongue!'' said the King, ''that only makes the matter worse. You MUST have meant some mischief."}], "16": [{"value": 454}], "21": [{"value": 1}], "22": [{"value": 2}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (29, NULL, 'resource_create', '{"resource": {"id": 24, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Public-key bottom-line leverage"}], "13": [{"value": "I do,'' said Alice indignantly. ''Let me alone!'' ''Serpent, I say again!'' repeated the Pigeon, but in a court of justice before, but she was considering in her pocket, and pulled out a race-course, in."}], "16": [{"value": 743}], "21": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (30, NULL, 'resource_create', '{"resource": {"id": 25, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Down-sized non-volatile opensystem"}], "13": [{"value": "CAN I have dropped them, I wonder?'' As she said to one of the shepherd boy--and the sneeze of the birds hurried off at once, she found herself in a trembling voice, ''Let us get to the rose-tree, she."}], "16": [{"value": 240}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (31, NULL, 'resource_create', '{"resource": {"id": 26, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Centralized 24/7 model"}], "13": [{"value": "I''m NOT a serpent, I tell you!'' But she waited for some minutes. Alice thought to herself. (Alice had no pictures or conversations in it, and fortunately was just in time to avoid shrinking away."}], "16": [{"value": 370}], "22": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');
INSERT INTO audit VALUES (32, NULL, 'resource_create', '{"resource": {"id": 27, "kindId": 1, "places": ["y1oosxtgf"], "contents": {"12": [{"value": "Persevering clear-thinking function"}], "13": [{"value": "Gryphon, and the blades of grass, but she was peering about anxiously among the branches, and every now and then nodded. ''It''s no business there, at any rate a book of rules for shutting people up."}], "16": [{"value": 481}], "21": [{"value": 1}]}, "resourceClass": "books"}}', true, '2018-06-18 13:13:53+02');


--
-- Data for Name: language; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO language VALUES ('PL', 'polski', 'PL');
INSERT INTO language VALUES ('GB', 'english', 'EN');


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO metadata VALUES (-1, 'relationship', '{"EN": "Parent resource", "PL": "Zasób nadrzędny"}', '[]', '[]', 'Parent', NULL, -1, NULL, '[]', false, '', false);
INSERT INTO metadata VALUES (-2, 'text', '{"EN": "Username", "PL": "Nazwa użytkownika"}', '[]', '[]', 'Username', NULL, -1, NULL, '[]', true, 'users', false);
INSERT INTO metadata VALUES (1, 'text', '{"EN": "Imię", "PL": "Imię"}', '[]', '[]', 'Imię', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (2, 'text', '{"EN": "Nazwisko", "PL": "Nazwisko"}', '[]', '[]', 'Nazwisko', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (3, 'text', '{"EN": "Email", "PL": "Email"}', '[]', '[]', 'Email', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (4, 'text', '{"EN": "Miasto", "PL": "Miasto"}', '[]', '[]', 'Miasto', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (5, 'text', '{"EN": "Ulica", "PL": "Ulica"}', '[]', '[]', 'Ulica', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (6, 'text', '{"EN": "Pesel", "PL": "Pesel"}', '[]', '[]', 'Pesel', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (7, 'text', '{"EN": "Kategoria", "PL": "Kategoria"}', '[]', '[]', 'Kategoria', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (8, 'text', '{"EN": "Wydział", "PL": "Wydział"}', '[]', '[]', 'Wydział', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (9, 'text', '{"EN": "Identyfikator wydziału", "PL": "Identyfikator wydziału"}', '[]', '[]', 'Identyfikator wydziału', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (10, 'text', '{"EN": "Instytut", "PL": "Instytut"}', '[]', '[]', 'Instytut', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (11, 'text', '{"EN": "Identyfikator instytutu", "PL": "Identyfikator instytutu"}', '[]', '[]', 'Identyfikator instytutu', NULL, -1, NULL, '[]', false, 'users', false);
INSERT INTO metadata VALUES (26, 'text', '{"EN": "Title language", "PL": "Język tytułu"}', '[]', '[]', NULL, 17, -1, 12, '{"regex": "^[a-z]{3}$", "maxCount": 1}', false, 'books', true);
INSERT INTO metadata VALUES (12, 'text', '{"EN": "Title", "PL": "Tytuł"}', '{"EN": "The title of the book", "PL": "Tytuł książki"}', '{"EN": "Find it on the cover", "PL": "Znajdziesz go na okładce"}', 'Tytuł', NULL, 0, NULL, '{"regex": "", "maxCount": 1}', true, 'books', true);
INSERT INTO metadata VALUES (13, 'textarea', '{"EN": "Description", "PL": "Opis"}', '{"EN": "Tell me more", "PL": "Napisz coś więcej"}', '[]', 'Opis', NULL, 1, NULL, '{"maxCount": 3}', false, 'books', false);
INSERT INTO metadata VALUES (14, 'date', '{"EN": "Publish date", "PL": "Data wydania"}', '[]', '[]', 'Data wydania', NULL, 2, NULL, '{"maxCount": 1}', false, 'books', false);
INSERT INTO metadata VALUES (15, 'boolean', '{"EN": "Hard cover", "PL": "Twarda okładka"}', '[]', '[]', 'Czy ma twardą okładkę?', NULL, 3, NULL, '{"maxCount": 1}', false, 'books', false);
INSERT INTO metadata VALUES (16, 'integer', '{"EN": "Number of pages", "PL": "Liczba stron"}', '[]', '[]', 'Liczba stron', NULL, 4, NULL, '{"maxCount": 1}', false, 'books', false);
INSERT INTO metadata VALUES (17, 'text', '{"EN": "Language", "PL": "Język"}', '[]', '[]', 'Język', NULL, 5, NULL, '{"regex": "^[a-z]{3}$", "maxCount": 1}', false, 'books', true);
INSERT INTO metadata VALUES (19, 'file', '{"EN": "Cover", "PL": "Okładka"}', '[]', '[]', 'Okładka', NULL, 7, NULL, '[]', false, 'books', false);
INSERT INTO metadata VALUES (20, 'text', '{"EN": "Category name", "PL": "Nazwa kategorii"}', '[]', '[]', 'Nazwa kategorii', NULL, 8, NULL, '{"regex": "", "maxCount": 1}', false, 'books', false);
INSERT INTO metadata VALUES (25, 'relationship', '{"EN": "Related book", "PL": "Powiązana książka"}', '[]', '[]', 'Powiązana książka', NULL, 13, NULL, '[]', true, 'books', false);
INSERT INTO metadata VALUES (27, 'text', '{"EN": "Name", "PL": "Nazwa"}', '[]', '[]', 'Nazwa', NULL, -1, NULL, '{"regex": "", "maxCount": 1}', false, 'dictionaries', false);
INSERT INTO metadata VALUES (28, 'text', '{"EN": "Abbreviation", "PL": "Nazwa skrótowa"}', '[]', '[]', 'Skrót', NULL, -1, NULL, '{"regex": "^[A-Z]{2,6}$"}', true, 'dictionaries', false);
INSERT INTO metadata VALUES (18, 'relationship', '{"EN": "See also", "PL": "Zobacz też"}', '[]', '[]', 'Zobacz też', NULL, 6, NULL, '{"resourceKind": [1]}', false, 'books', false);
INSERT INTO metadata VALUES (29, 'relationship', '{"EN": "University", "PL": "Uczelnia"}', '[]', '[]', 'Uczelnia', NULL, -1, NULL, '{"maxCount": 1, "resourceKind": [5]}', false, 'dictionaries', false);
INSERT INTO metadata VALUES (23, 'relationship', '{"EN": "Issued on", "PL": "Wydział wydający"}', '[]', '[]', 'Wydział wydający', NULL, 11, NULL, '{"resourceKind": [4]}', false, 'books', false);
INSERT INTO metadata VALUES (24, 'relationship', '{"EN": "Publishing house", "PL": "Wydawnictwo"}', '[]', '[]', 'Wydawnictwo', NULL, 12, NULL, '{"resourceKind": [6]}', false, 'books', false);
INSERT INTO metadata VALUES (22, 'relationship', '{"EN": "Supervisor", "PL": "Nadzorujący"}', '[]', '[]', 'Nadzorujący', NULL, 10, NULL, '{"resourceKind": [-1, 7]}', false, 'books', false);
INSERT INTO metadata VALUES (21, 'relationship', '{"EN": "Scanner", "PL": "Skanista"}', '[]', '[]', 'Skanista', NULL, 9, NULL, '{"resourceKind": [-1, 7]}', false, 'books', false);
INSERT INTO metadata VALUES (-3, 'relationship', '{"EN": "Group member", "PL": "Członek grupy"}', '[]', '[]', 'Group member', NULL, -1, NULL, '{"resourceKind": [7]}', false, 'users', false);


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
INSERT INTO migration_versions VALUES ('20180215094822');
INSERT INTO migration_versions VALUES ('20180216235421');
INSERT INTO migration_versions VALUES ('20180219213547');
INSERT INTO migration_versions VALUES ('20180313122800');
INSERT INTO migration_versions VALUES ('20180321122132');
INSERT INTO migration_versions VALUES ('20180413093410');
INSERT INTO migration_versions VALUES ('20180422131227');
INSERT INTO migration_versions VALUES ('20180424133832');
INSERT INTO migration_versions VALUES ('20180424170831');
INSERT INTO migration_versions VALUES ('20180428200114');
INSERT INTO migration_versions VALUES ('20180503231556');
INSERT INTO migration_versions VALUES ('20180523083737');
INSERT INTO migration_versions VALUES ('20180525085546');
INSERT INTO migration_versions VALUES ('20180528200825');
INSERT INTO migration_versions VALUES ('20180528200826');


--
-- Data for Name: resource; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO resource VALUES (3, -1, '{"-2": [{"value": "tester"}]}', NULL, 'users');
INSERT INTO resource VALUES (5, 7, '{"-2": [{"value": "Administratorzy"}]}', NULL, 'users');
INSERT INTO resource VALUES (6, 7, '{"-2": [{"value": "Skaniści"}]}', NULL, 'users');
INSERT INTO resource VALUES (1, -1, '{"-2": [{"value": "admin"}], "-3": [{"value": 5}, {"value": 6}]}', NULL, 'users');
INSERT INTO resource VALUES (2, -1, '{"-2": [{"value": "budynek"}], "-3": [{"value": 6}]}', NULL, 'users');
INSERT INTO resource VALUES (4, -1, '{"-2": [{"value": "skaner"}], "-3": [{"value": 6}]}', NULL, 'users');
INSERT INTO resource VALUES (7, 5, '{"27": [{"value": "Akademia Górniczo Hutnicza"}], "28": [{"value": "AGH"}]}', NULL, 'dictionaries');
INSERT INTO resource VALUES (8, 5, '{"27": [{"value": "Politechnika Krakowska"}], "28": [{"value": "PK"}]}', NULL, 'dictionaries');
INSERT INTO resource VALUES (9, 4, '{"27": [{"value": "Informatyki, Elektroniki i Telekomunikacji"}], "28": [{"value": "IET"}], "29": [{"value": 7}]}', NULL, 'dictionaries');
INSERT INTO resource VALUES (10, 4, '{"27": [{"value": "Elektroniki, Automatyki i Inżynierii Biomedycznej"}], "28": [{"value": "EAIB"}], "29": [{"value": 7}]}', NULL, 'dictionaries');
INSERT INTO resource VALUES (11, 6, '{"27": [{"value": "Wydawnictwo Zakładu Narodowego im. Ossolińskich"}]}', NULL, 'dictionaries');
INSERT INTO resource VALUES (13, 1, '{"12": [{"value": "PHP - to można leczyć!"}], "13": [{"value": "Poradnik dla cierpiących na zwyrodnienie interpretera."}], "15": [{"value": true}], "16": [{"value": 1337}], "18": [{"value": 12}], "21": [{"value": 4}], "22": [{"value": 2}], "25": [{"value": 12}]}', '{"y1oosxtgf": true}', 'books');
INSERT INTO resource VALUES (14, 2, '{"12": [{"value": "Python dla opornych"}], "23": [{"value": 9}]}', NULL, 'books');
INSERT INTO resource VALUES (15, 3, '{"20": [{"value": "E-booki"}]}', NULL, 'books');
INSERT INTO resource VALUES (16, 1, '{"-1": [{"value": 15}], "12": [{"value": "\"Mogliśmy użyć Webpacka\" i inne spóźnione mądrości"}]}', '{"y1oosxtgf": true}', 'books');
INSERT INTO resource VALUES (17, 1, '{"-1": [{"value": 15}], "12": [{"value": "Pair programming: jak równocześnie pisać na jednej klawiaturze w dwie osoby"}]}', '{"y1oosxtgf": true}', 'books');
INSERT INTO resource VALUES (12, 1, '{"12": [{"value": "PHP i MySQL"}], "13": [{"value": "Błędy młodości..."}], "16": [{"value": 404}], "21": [{"value": 4}], "22": [{"value": 2}]}', '{"qqd3yk499": 1}', 'books');
INSERT INTO resource VALUES (18, 1, '{"12": [{"value": "Realigned assymetric database"}], "13": [{"value": "Hatter. ''You MUST remember,'' remarked the King, ''unless it was looking about for a minute or two she stood watching them, and then I''ll tell him--it was for bringing the cook till his eyes very wide."}], "16": [{"value": 437}], "21": [{"value": 1}], "22": [{"value": 1}]}', '{"xo77kutzk": true}', 'books');
INSERT INTO resource VALUES (20, 1, '{"12": [{"value": "Phased modular moderator"}], "13": [{"value": "Alice. ''I''ve read that in the air, mixed up with the next moment she felt that she was appealed to by the White Rabbit, ''but it seems to grin, How neatly spread his claws, And welcome little fishes."}], "16": [{"value": 851}], "21": [{"value": 1}], "22": [{"value": 1}]}', '{"y1oosxtgf": true}', 'books');
INSERT INTO resource VALUES (19, 1, '{"12": [{"value": "Team-oriented multi-state product"}], "13": [{"value": "Hatter, who turned pale and fidgeted. ''Give your evidence,'' said the Mock Turtle said: ''advance twice, set to partners--'' ''--change lobsters, and retire in same order,'' continued the Hatter, ''you."}], "16": [{"value": 517}], "21": [{"value": 1}]}', '{"ss9qm7r78": true}', 'books');
INSERT INTO resource VALUES (21, 1, '{"12": [{"value": "Switchable solution-oriented flexibility"}], "13": [{"value": "However, when they saw Alice coming. ''There''s PLENTY of room!'' said Alice desperately: ''he''s perfectly idiotic!'' And she began fancying the sort of thing that would happen: ''\"Miss Alice! Come here."}], "16": [{"value": 202}], "21": [{"value": 2}], "22": [{"value": 2}]}', '{"j70hlpsvu": true}', 'books');
INSERT INTO resource VALUES (22, 1, '{"12": [{"value": "Front-line neutral data-warehouse"}], "13": [{"value": "Beautiful, beauti--FUL SOUP!'' ''Chorus again!'' cried the Gryphon, with a soldier on each side, and opened their eyes and mouths so VERY nearly at the White Rabbit; ''in fact, there''s nothing written."}], "16": [{"value": 204}], "21": [{"value": 1}], "22": [{"value": 2}]}', '{"9qq9ipqa3": true}', 'books');
INSERT INTO resource VALUES (23, 1, '{"12": [{"value": "Focused systemic opensystem"}], "13": [{"value": "As there seemed to be Involved in this affair, He trusts to you never to lose YOUR temper!'' ''Hold your tongue!'' said the King, ''that only makes the matter worse. You MUST have meant some mischief."}], "16": [{"value": 454}], "21": [{"value": 1}], "22": [{"value": 2}]}', '{"j70hlpsvu": true}', 'books');
INSERT INTO resource VALUES (24, 1, '{"12": [{"value": "Public-key bottom-line leverage"}], "13": [{"value": "I do,'' said Alice indignantly. ''Let me alone!'' ''Serpent, I say again!'' repeated the Pigeon, but in a court of justice before, but she was considering in her pocket, and pulled out a race-course, in."}], "16": [{"value": 743}], "21": [{"value": 1}]}', '{"ss9qm7r78": true}', 'books');
INSERT INTO resource VALUES (25, 1, '{"12": [{"value": "Down-sized non-volatile opensystem"}], "13": [{"value": "CAN I have dropped them, I wonder?'' As she said to one of the shepherd boy--and the sneeze of the birds hurried off at once, she found herself in a trembling voice, ''Let us get to the rose-tree, she."}], "16": [{"value": 240}]}', '{"9qq9ipqa3": true}', 'books');
INSERT INTO resource VALUES (27, 1, '{"12": [{"value": "Persevering clear-thinking function"}], "13": [{"value": "Gryphon, and the blades of grass, but she was peering about anxiously among the branches, and every now and then nodded. ''It''s no business there, at any rate a book of rules for shutting people up."}], "16": [{"value": 481}], "21": [{"value": 1}]}', '{"y1oosxtgf": true}', 'books');
INSERT INTO resource VALUES (26, 1, '{"12": [{"value": "Centralized 24/7 model"}], "13": [{"value": "I''m NOT a serpent, I tell you!'' But she waited for some minutes. Alice thought to herself. (Alice had no pictures or conversations in it, and fortunately was just in time to avoid shrinking away."}], "16": [{"value": 370}], "22": [{"value": 1}]}', '{"xo77kutzk": true}', 'books');


--
-- Data for Name: resource_kind; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO resource_kind VALUES (-1, '{"EN": "user", "PL": "user"}', NULL, 'users', '{"header": "{{r|mUsername}}", "dropdown": "{{r|mUsername}}"}', '[{"id": -2}, {"id": -3}, {"id": -1}, {"id": 1}, {"id": 2}, {"id": 3}, {"id": 4}, {"id": 5}, {"id": 6}, {"id": 7}, {"id": 8}, {"id": 9}, {"id": 10}, {"id": 11}]');
INSERT INTO resource_kind VALUES (1, '{"EN": "Book", "PL": "Książka"}', 1, 'books', '{"header": "{{r|m12}}", "dropdown": "{{r|m12}} (ID: {{r.id}})"}', '[{"id": 12}, {"id": 13}, {"id": 14}, {"id": 15}, {"id": 16}, {"id": 17}, {"id": 18}, {"id": 25}, {"id": 19}, {"id": 24}, {"id": 21}, {"id": 22}, {"id": -1}]');
INSERT INTO resource_kind VALUES (2, '{"EN": "Forbidden book", "PL": "Zakazana książka"}', NULL, 'books', '{"header": "{{r|m12}}", "dropdown": "{{r|m12}} (ID: {{r.id}})"}', '[{"id": -1}, {"id": 12}, {"id": 23}]');
INSERT INTO resource_kind VALUES (3, '{"EN": "Category", "PL": "Kategoria"}', NULL, 'books', '{"header": "{{r|m20}}", "dropdown": "{{r|m20}} (ID: {{r.id}})"}', '[{"id": -1, "constraints": {"resourceKind": [1, 2]}}, {"id": 20}]');
INSERT INTO resource_kind VALUES (4, '{"EN": "Department", "PL": "Wydział"}', NULL, 'dictionaries', '{"header": "{{r|m27}} ({{r|m28}})", "dropdown": "{{r|m28}} (ID: {{r.id}})"}', '[{"id": 27}, {"id": 28}, {"id": 29}, {"id": -1}]');
INSERT INTO resource_kind VALUES (5, '{"EN": "University", "PL": "Uczelnia"}', NULL, 'dictionaries', '{"header": "{{r|m27}} ({{r|m28}})", "dropdown": "{{r|m28}} (ID: {{r.id}})"}', '[{"id": 27, "label": {"PL": "Nazwa uczelni"}}, {"id": 28}, {"id": -1}]');
INSERT INTO resource_kind VALUES (6, '{"EN": "Publishing house", "PL": "Wydawnictwo"}', NULL, 'dictionaries', '{"header": "{{r|m27}}", "dropdown": "{{r|m27}} (ID: {{r.id}})"}', '[{"id": 27, "label": {"PL": "Nazwa wydawnictwa"}}, {"id": -1}]');
INSERT INTO resource_kind VALUES (7, '{"EN": "User group", "PL": "Grupa użytkowników"}', NULL, 'users', '{"header": "{{r|mUsername}} (ID: {{r.id}})", "dropdown": "{{r|mUsername}} (ID: {{r.id}})"}', '[{"id": -2}, {"id": -1}]');


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO role VALUES ('{"PL": "Admin"}', -1);
INSERT INTO role VALUES ('{"PL": "Operator"}', -2);
INSERT INTO role VALUES ('{"EN": "Acceptor", "PL": "Akceptujący"}', 1);
INSERT INTO role VALUES ('{"EN": "Librarian", "PL": "Bibliotekarz"}', 2);
INSERT INTO role VALUES ('{"EN": "OCR operator", "PL": "Operator OCR"}', 3);
INSERT INTO role VALUES ('{"EN": "Publisher", "PL": "Publikujący"}', 4);
INSERT INTO role VALUES ('{"EN": "Scanner", "PL": "Skanista"}', 5);
INSERT INTO role VALUES ('{"EN": "Tester", "PL": "Tester"}', 6);


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "user" VALUES (1038, '$2y$13$WKPlhcdSI/trCCLRCBsxTuv75zo6OzYW6h8DKlBdah4nq1ADOP/4G', true, 1);
INSERT INTO "user" VALUES (1039, '$2y$13$LsIkebfE68eoG7j59HqqYuemTxnQ6LC5Wc4FC0lkHTKW8d3zh4bei', true, 2);
INSERT INTO "user" VALUES (1040, '$2y$13$0Vv3mVVGzQZjW/YM6nJLXug9ZZp.XoLDK1aiFaPSo3XNs56Ls3hJq', true, 3);
INSERT INTO "user" VALUES (1041, '$2y$13$rETQ5gHzkkjjULq6ZarBGeBikPBNVzcK/VSfsXsS.SuNK5qeAwSrC', true, 4);


--
-- Data for Name: user_role; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO user_role VALUES (1038, -1);
INSERT INTO user_role VALUES (1038, -2);
INSERT INTO user_role VALUES (1040, -2);
INSERT INTO user_role VALUES (1040, 6);
INSERT INTO user_role VALUES (1041, -2);
INSERT INTO user_role VALUES (1041, 5);


--
-- Data for Name: workflow; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO workflow VALUES (1, '{"EN": "Book workflow", "PL": "Pełny obieg książki"}', '[{"id": "y1oosxtgf", "label": {"EN": "Imported", "PL": "Zaimportowana"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [12], "autoAssignMetadataIds": []}, {"id": "lb1ovdqcy", "label": {"EN": "Ready to scan", "PL": "Do skanowania"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [21, 22], "autoAssignMetadataIds": []}, {"id": "qqd3yk499", "label": {"EN": "Scanned", "PL": "Zeskanowana"}, "pluginsConfig": [], "lockedMetadataIds": [22], "assigneeMetadataIds": [21], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "9qq9ipqa3", "label": {"EN": "Require rescan", "PL": "Wymaga ponownego skanowania"}, "pluginsConfig": [], "lockedMetadataIds": [21, 22], "assigneeMetadataIds": [], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "ss9qm7r78", "label": {"EN": "Verified", "PL": "Zweryfikowana"}, "pluginsConfig": [], "lockedMetadataIds": [21, 22], "assigneeMetadataIds": [], "requiredMetadataIds": [19], "autoAssignMetadataIds": []}, {"id": "jvz160sl4", "label": {"EN": "Recognized", "PL": "Rozpoznana"}, "pluginsConfig": [], "lockedMetadataIds": [19, 21, 22], "assigneeMetadataIds": [], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "xo77kutzk", "label": {"EN": "Accepted", "PL": "Zaakceptowana"}, "pluginsConfig": [], "lockedMetadataIds": [19, 21, 22], "assigneeMetadataIds": [], "requiredMetadataIds": [], "autoAssignMetadataIds": []}, {"id": "j70hlpsvu", "label": {"EN": "Published", "PL": "Opublikowana"}, "pluginsConfig": [], "lockedMetadataIds": [12, 19, 21, 22], "assigneeMetadataIds": [], "requiredMetadataIds": [], "autoAssignMetadataIds": []}]', '[{"id": "e7d756ed-d6b3-4f2f-9517-679311e88b17", "tos": ["lb1ovdqcy"], "froms": ["y1oosxtgf"], "label": {"EN": "Attach metrics", "PL": "Dołącz metryczkę"}, "permittedRoleIds": [2, -1]}, {"id": "d3f73249-d10f-4d4b-8b63-be60b4c02081", "tos": ["qqd3yk499"], "froms": ["lb1ovdqcy"], "label": {"EN": "Scan", "PL": "Skanuj"}, "permittedRoleIds": [5, -1]}, {"id": "b2725b84-c470-40f7-b7b5-3850e0f2754c", "tos": ["9qq9ipqa3"], "froms": ["qqd3yk499"], "label": {"EN": "Reject", "PL": "Odrzuć"}, "permittedRoleIds": [1, -1]}, {"id": "9faac2d6-3a58-4ead-9aa2-9181c778a2e7", "tos": ["qqd3yk499"], "froms": ["9qq9ipqa3"], "label": {"EN": "Rescan", "PL": "Skanuj ponownie"}, "permittedRoleIds": [5, -1]}, {"id": "1b59e8f1-26e9-4018-a6cf-a39ef8e8521b", "tos": ["ss9qm7r78"], "froms": ["qqd3yk499"], "label": {"EN": "Verify", "PL": "Zweryfikuj"}, "permittedRoleIds": [1, 6, -1]}, {"id": "4d96170b-f486-443d-ad0c-7e882487f5e1", "tos": ["jvz160sl4"], "froms": ["ss9qm7r78"], "label": {"EN": "Recognize", "PL": "Rozpoznaj"}, "permittedRoleIds": [3, -1]}, {"id": "e603b0a3-d04f-495c-8caa-a67e604e3c87", "tos": ["xo77kutzk"], "froms": ["jvz160sl4"], "label": {"EN": "Accept", "PL": "Zaakceptuj"}, "permittedRoleIds": [1, -1]}, {"id": "ce30b481-8dde-40e4-ab7c-0bc90e918431", "tos": ["j70hlpsvu"], "froms": ["xo77kutzk"], "label": {"EN": "Publish", "PL": "Opublikuj"}, "permittedRoleIds": [4, -1]}, {"id": "83c98637-6173-40b2-8840-cc2ae914bcc4", "tos": ["xo77kutzk"], "froms": ["j70hlpsvu"], "label": {"EN": "Unpublish", "PL": "Zdejmij"}, "permittedRoleIds": [4, -1]}]', '{"y1oosxtgf":{"x":51.01815994306967,"y":175.384765625},"lb1ovdqcy":{"x":252.21875,"y":175.60595703125},"qqd3yk499":{"x":266.026612773572,"y":59.700392994713134},"9qq9ipqa3":{"x":44.82338335188286,"y":70.47171223529797},"ss9qm7r78":{"x":405.75008979779426,"y":58.87617816576146},"jvz160sl4":{"x":558.795899588251,"y":55.28335247948525},"xo77kutzk":{"x":554.1022011563498,"y":203.39161358806604},"j70hlpsvu":{"x":378.4111711160704,"y":135.956784665596}}', 'data:image/png;base64,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', 'books');


--
-- Name: audit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('audit_id_seq', 32, true);


--
-- Name: metadata_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('metadata_id_seq', 29, true);


--
-- Name: resource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('resource_id_seq', 27, true);


--
-- Name: resource_kind_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('resource_kind_id_seq', 7, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('role_id_seq', 6, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('user_id_seq', 1041, true);


--
-- Name: workflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('workflow_id_seq', 1, true);


--
-- Name: audit audit_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT audit_pkey PRIMARY KEY (id);


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
-- Name: role role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: user_role user_role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_role
    ADD CONSTRAINT user_role_pkey PRIMARY KEY (user_id, role_id);


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
-- Name: idx_2de8c6a3a76ed395; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_2de8c6a3a76ed395 ON user_role USING btree (user_id);


--
-- Name: idx_34e41c632c7c2cba; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_34e41c632c7c2cba ON resource_kind USING btree (workflow_id);


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
-- Name: user_role fk_2de8c6a3a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_role
    ADD CONSTRAINT fk_2de8c6a3a76ed395 FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE;


--
-- Name: resource_kind fk_34e41c632c7c2cba; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource_kind
    ADD CONSTRAINT fk_34e41c632c7c2cba FOREIGN KEY (workflow_id) REFERENCES workflow(id);


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
-- Name: user_role role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_role
    ADD CONSTRAINT role_fk FOREIGN KEY (role_id) REFERENCES role(id);


--
-- PostgreSQL database dump complete
--

