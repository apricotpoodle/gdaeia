CREATE OR REPLACE
ALGORITHM = UNDEFINED
VIEW `daetf`.`currentvalidationroles` AS
SELECT
    af.id AS applicationform_id,
    af.department_id,
    s.role_id AS validator_role_id,
    s.sequence_number AS validation_sequence,
    2 AS validationstatus_id,
    wr.state = 'en_attente' AS en_cours,
    wr.state = 'acceptee' AS accepted,
    wr.state = 'refusee' AS rejected
FROM daetf.validation_workflow_runs wr
JOIN daetf.applicationforms af
    ON af.id = wr.applicationform_id
JOIN daetf.applicationvalidationsteps s
    ON s.validation_workflow_run_id = wr.id
WHERE s.state = 'en_attente';


CREATE OR REPLACE
ALGORITHM = UNDEFINED
VIEW `daetf`.`validation_visas` AS
SELECT
    s.applicationform_id,
    s.sequence_number AS sequence,
    s.role_id,
    CONCAT(u.firstname, ' ', u.lastname) AS op_name,
    r.name AS role_name,
    status.name AS status_name,
    v.validated AS validated_at
FROM daetf.validations v
JOIN daetf.applicationvalidationsteps s
    ON s.id = v.applicationvalidationstep_id
JOIN daetf.roles r
    ON r.id = s.role_id
LEFT JOIN daetf.users u
    ON u.id = v.user_id
LEFT JOIN daetf.validationstatuses status
    ON status.id = v.validationstatus_id
WHERE v.deleted IS NULL
ORDER BY
    s.applicationform_id,
    s.sequence_number,
    s.role_id;


SELECT *
FROM daetf.applicationformstatuses
WHERE applicationform_id = 32;

SELECT *
FROM daetf.currentvalidationroles
WHERE applicationform_id = 32;

SELECT *
FROM daetf.validation_visas
WHERE applicationform_id = 32;
