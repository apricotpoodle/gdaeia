-- Extractions depuis l'ancienne base `dae`.
--
-- Exécuter chaque SELECT séparément et exporter le résultat en CSV UTF-8 avec
-- la première ligne contenant les noms de colonnes. Ces requêtes n'utilisent
-- pas INTO OUTFILE afin de rester compatibles avec les droits SQL habituels.

-- 01-users.csv : ne jamais sélectionner USR_PWD ni les jetons.
SELECT
    u.USR_ID AS source_user_id,
    NULLIF(TRIM(u.USR_NOM), '') AS source_username,
    LOWER(NULLIF(TRIM(u.USR_ADR), '')) AS email,
    NULLIF(TRIM(u.USR_PRENOM), '') AS firstname,
    NULLIF(TRIM(u.USR_PATRONYME), '') AS lastname,
    u.RLE_ID AS source_role_id,
    r.RLE_CODE AS source_role_code,
    r.RLE_LBL AS source_role_name,
    u.USR_BASE AS source_user_editable,
    CASE WHEN u.USR_ADR IS NULL OR TRIM(u.USR_ADR) = '' THEN 0 ELSE 1 END
        AS importable_email
FROM dae.ts_user_usr u
LEFT JOIN dae.tr_role_rle r ON r.RLE_ID = u.RLE_ID
ORDER BY u.USR_ID;

-- 02-roles.csv
SELECT
    r.RLE_ID AS source_role_id,
    NULLIF(TRIM(r.RLE_CODE), '') AS source_role_code,
    NULLIF(TRIM(r.RLE_LBL), '') AS source_role_name,
    r.RLE_ORD AS source_role_order,
    r.RLE_BASE AS source_role_base
FROM dae.tr_role_rle r
ORDER BY r.RLE_ORD, r.RLE_ID;

-- 03-user-roles.csv : rôles contextualisés par société.
SELECT
    sur.USR_ID AS source_user_id,
    sur.RLE_ID AS source_role_id,
    r.RLE_CODE AS source_role_code,
    sur.STN_ID AS source_company_id,
    stn.STN_CODE AS source_company_code,
    stn.STN_LBL AS source_company_name
FROM dae.tj_stn_usr_rle_sur sur
LEFT JOIN dae.tr_role_rle r ON r.RLE_ID = sur.RLE_ID
LEFT JOIN dae.tr_nom_societe_stn stn ON stn.STN_ID = sur.STN_ID
ORDER BY sur.USR_ID, sur.STN_ID, sur.RLE_ID;

-- 04-user-groups.csv
SELECT
    gu.USR_ID AS source_user_id,
    gu.GRP_ID AS source_group_id,
    g.GRP_CODE AS source_group_code,
    g.GRP_LBL AS source_group_name
FROM dae.tj_gru_user_gru gu
LEFT JOIN dae.tr_groupe_user_grp g ON g.GRP_ID = gu.GRP_ID
ORDER BY gu.USR_ID, gu.GRP_ID;

-- 05-user-service-visibility.csv
SELECT
    usv.USR_ID AS source_user_id,
    usv.STN_ID AS source_company_id,
    stn.STN_CODE AS source_company_code,
    stn.STN_LBL AS source_company_name,
    usv.SST_ID AS source_company_service_id
FROM dae.tj_usr_srv_visible_usv usv
LEFT JOIN dae.tr_nom_societe_stn stn ON stn.STN_ID = usv.STN_ID
ORDER BY usv.USR_ID, usv.STN_ID, usv.SST_ID;

-- 06-user-cgr-service-scope.csv
SELECT
    csu.USR_ASS_ID AS source_assistant_user_id,
    csu.USR_CSV_ID AS source_service_manager_user_id,
    csu.STN_ID AS source_company_id,
    stn.STN_CODE AS source_company_code,
    csu.SRV_ID AS source_service_id,
    srv.SRV_CODE AS source_service_code,
    srv.SRV_LBL AS source_service_name,
    csu.CGR_ID AS source_analytic_code_id,
    cgr.CGR_CODE AS source_analytic_code,
    cgr.CGR_LBL AS source_analytic_name
FROM dae.tj_cgr_srv_usr_csu csu
LEFT JOIN dae.tr_nom_societe_stn stn ON stn.STN_ID = csu.STN_ID
LEFT JOIN dae.tr_service_srv srv ON srv.SRV_ID = csu.SRV_ID
LEFT JOIN dae.tr_code_analytique_cgr cgr ON cgr.CGR_ID = csu.CGR_ID
ORDER BY csu.STN_ID, csu.SRV_ID, csu.CGR_ID, csu.USR_ASS_ID, csu.USR_CSV_ID;

-- 07-reference-companies.csv
SELECT STN_ID AS source_company_id, STN_CODE AS source_company_code,
       STN_LBL AS source_company_name, STN_ORD AS source_company_order
FROM dae.tr_nom_societe_stn
ORDER BY STN_ORD, STN_ID;

-- 08-reference-services.csv
SELECT SRV_ID AS source_service_id, SRV_CODE AS source_service_code,
       SRV_LBL AS source_service_name, SRV_ORD AS source_service_order
FROM dae.tr_service_srv
ORDER BY SRV_ORD, SRV_ID;

-- 09-reference-groups.csv
SELECT GRP_ID AS source_group_id, GRP_CODE AS source_group_code,
       GRP_LBL AS source_group_name, GRP_ORD AS source_group_order
FROM dae.tr_groupe_user_grp
ORDER BY GRP_ORD, GRP_ID;

-- 10-quality-duplicate-emails.csv : à traiter avant import.
SELECT
    LOWER(TRIM(USR_ADR)) AS email,
    COUNT(*) AS source_user_count,
    GROUP_CONCAT(USR_ID ORDER BY USR_ID SEPARATOR ',') AS source_user_ids
FROM dae.ts_user_usr
WHERE USR_ADR IS NOT NULL AND TRIM(USR_ADR) <> ''
GROUP BY LOWER(TRIM(USR_ADR))
HAVING COUNT(*) > 1
ORDER BY email;

-- 11-quality-missing-emails.csv : ces utilisateurs ne sont pas importables
-- comme comptes connectables sans correction manuelle.
SELECT
    USR_ID AS source_user_id,
    USR_NOM AS source_username,
    USR_PRENOM AS firstname,
    USR_PATRONYME AS lastname,
    RLE_ID AS source_role_id
FROM dae.ts_user_usr
WHERE USR_ADR IS NULL OR TRIM(USR_ADR) = ''
ORDER BY USR_ID;

-- 12-quality-orphaned-role-links.csv : contrôle de l'intégrité des liens.
SELECT
    sur.USR_ID AS source_user_id,
    sur.RLE_ID AS source_role_id,
    sur.STN_ID AS source_company_id
FROM dae.tj_stn_usr_rle_sur sur
LEFT JOIN dae.ts_user_usr u ON u.USR_ID = sur.USR_ID
LEFT JOIN dae.tr_role_rle r ON r.RLE_ID = sur.RLE_ID
WHERE u.USR_ID IS NULL OR r.RLE_ID IS NULL
ORDER BY sur.USR_ID, sur.RLE_ID, sur.STN_ID;
