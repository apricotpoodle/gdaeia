-- daetf.applicationforms definition

CREATE TABLE `applicationforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiant unique de la demande de recrutement',
  `department_id` int NOT NULL COMMENT 'Identifiant du département/service rattaché',
  `user_id` int NOT NULL COMMENT 'Identifiant de l''utilisateur émetteur/demandeur',
  `cgr` varchar(255) DEFAULT NULL COMMENT 'Code analytique CGR d''affectation',
  `contracttype_id` int NOT NULL COMMENT 'Identifiant du type de contrat (CDI, CDD...)',
  `hiringreason_id` int NOT NULL COMMENT 'Identifiant du motif principal d''embauche',
  `reasonforreplacement` varchar(255) DEFAULT NULL COMMENT 'Précision sur la personne ou le motif en cas de remplacement',
  `budgetfeature_id` int NOT NULL DEFAULT '1' COMMENT 'Identifiant de la caractéristique budgétaire (Au/Hors budget)',
  `jobtitle` varchar(255) NOT NULL COMMENT 'Intitulé ou description du poste à pourvoir',
  `professionalcategory_id` int NOT NULL COMMENT 'Identifiant de la catégorie professionnelle (Cadre, Employé...)',
  `worktime_id` int NOT NULL COMMENT 'Identifiant du régime de temps de travail (Complet/Partiel)',
  `workingtimedistribution` varchar(255) DEFAULT NULL COMMENT 'Répartition horaire du temps de travail',
  `grossremuneration` decimal(19, 4) NOT NULL DEFAULT '0.0000' COMMENT 'Montant de la rémunération brute',
  `period_id` int NOT NULL COMMENT 'Identifiant de la périodicité de rémunération (Mensuel, Annuel...)',
  `qualification` varchar(255) DEFAULT NULL COMMENT 'Niveau de qualification ou diplôme requis',
  `begin_at` date DEFAULT NULL COMMENT 'Date de début souhaitée du contrat',
  `end_at` date DEFAULT NULL COMMENT 'Date de fin de contrat (si CDD)',
  `applicantname` varchar(255) DEFAULT NULL COMMENT 'Nom et prénom du salarié pressenti',
  `yesno_id` int NOT NULL COMMENT 'Identifiant d''option binaire/validation',
  `deleted` datetime DEFAULT NULL COMMENT 'Horodatage de suppression logique (Soft Delete)',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de création de la fiche',
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP COMMENT 'Horodatage de dernière modification',
    `collaborator_id` bigint DEFAULT NULL COMMENT 'ID collaborateur interne concerne (CLB_ID)',
    `archived` datetime DEFAULT NULL COMMENT 'Horodatage d archivage de la fiche (DAE_ARCHIVE_IND)',
    PRIMARY KEY (`id`),
    KEY `applicationforms_department_id_IDX` (`department_id`)
        USING BTREE,
    FULLTEXT KEY `ft_applicationforms_global` (`jobtitle`,
    `applicantname`,
    `qualification`,
    `reasonforreplacement`)
) ENGINE = InnoDB AUTO_INCREMENT = 43 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.applicationvalidationsteps definition

CREATE TABLE `applicationvalidationsteps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `applicationform_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  `validationstatus_id` int unsigned NOT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `validationsequence_id` int unsigned NOT NULL,
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'gère chaque étape de validation d''une demande';
-- daetf.budgetfeatures definition

CREATE TABLE `budgetfeatures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.cgr_strategies definition

CREATE TABLE `cgr_strategies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL COMMENT 'Code technique unique (ex: STANDARD, SEM)',
  `name` varchar(64) NOT NULL COMMENT 'Nom lisible de la stratégie',
  `definition_json` json NOT NULL COMMENT 'Configuration JSON des champs requis',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Définit les stratégies de construction du CGR';
-- daetf.contracttypes definition

CREATE TABLE `contracttypes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.email_logs definition

CREATE TABLE `email_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content_text` text,
  `content_html` text,
  `error_message` text,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 638 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table enregistrant les informations des e-mails envoyés, y compris le sujet, le contenu en texte et en HTML, ainsi que les messages d''erreur éventuels.';
-- daetf.hiringreasons definition

CREATE TABLE `hiringreasons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.menus definition

CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `lft` int NOT NULL,
  `rght` int NOT NULL,
  `level` int DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT NULL,
  `dividor_before` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lft` (`lft`),
  KEY `parent_id` (`parent_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 24 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.periods definition

CREATE TABLE `periods` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.phinxlog definition

CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- daetf.professionalcategories definition

CREATE TABLE `professionalcategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.role_menus definition

CREATE TABLE `role_menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_menus_role_menu_dept_UN` (`role_id`,
    `menu_id`,
    `department_id`),
    KEY `user_id` (`role_id`),
    KEY `menu_id` (`menu_id`),
    KEY `idx_role_menus_dept` (`department_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 43 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.roles definition

CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  `sort` varchar(64) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.user_departments definition

CREATE TABLE `user_departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_departments_user_id_IDX` (`user_id`,
    `department_id`)
        USING BTREE,
    KEY `user_id` (`user_id`),
    KEY `department_id` (`department_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1728 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.users definition

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `issuperuser` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int NOT NULL DEFAULT '1',
  `token_expires` timestamp NULL DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 165 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.validations definition

CREATE TABLE `validations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `applicationform_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `validated` datetime DEFAULT NULL,
  `validationstatus_id` int DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `applicationform_id` (`applicationform_id`),
    KEY `validated` (`validationstatus_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1423 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.validationsequences definition

CREATE TABLE `validationsequences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `description` text,
  `role_id` int unsigned NOT NULL COMMENT 'Rôle Requis pour valider l''étape',
  `sequence` int NOT NULL DEFAULT '1' COMMENT 'ordre sequentel de validation',
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `validationsequences_UN` (`department_id`,
    `role_id`),
    KEY `validationsequences_department_id_IDX` (`department_id`,
    `sequence`)
        USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1289 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'définit l''ordre des séquences pour chaque department.';
-- daetf.validationstatuses definition

CREATE TABLE `validationstatuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table de référence des statuts de validation';
-- daetf.worktimes definition

CREATE TABLE `worktimes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.yesnos definition

CREATE TABLE `yesnos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `deleted` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.comments definition

CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL COMMENT 'ID du commentaire parent (0 ou NULL si premier niveau)',
  `model` varchar(64) NOT NULL COMMENT 'Nom du modele associe (ex: Applicationforms)',
  `foreign_key` int unsigned NOT NULL COMMENT 'ID de l enregistrement lie dans le modele',
  `type` varchar(32) NOT NULL DEFAULT 'GENERAL' COMMENT 'Type/Contexte (OBSERVATION, HIRING_REASON, PART_TIME, GENERAL)',
  `content` text NOT NULL COMMENT 'Contenu texte du commentaire',
  `user_id` int unsigned NOT NULL COMMENT 'ID de l auteur du commentaire',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de creation',
  `modified` timestamp NULL DEFAULT NULL COMMENT 'Horodatage de derniere modification',
  PRIMARY KEY (`id`),
  KEY `idx_comments_polymorphic` (`model`,
`foreign_key`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON
DELETE
    CASCADE ON
    UPDATE
        CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 10 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.email_recipients definition

CREATE TABLE `email_recipients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_log_id` int NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emaillog_id` (`email_log_id`),
  CONSTRAINT `email_recipients_ibfk_1` FOREIGN KEY (`email_log_id`) REFERENCES `email_logs` (`id`) ON
DELETE
    CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2006 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table contenant les destinataires des e-mails envoyés, avec une référence à l''e-mail correspondant dans la table emaillogs.';
-- daetf.field_authorizations definition

CREATE TABLE `field_authorizations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `resource` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `access_level` varchar(20) NOT NULL DEFAULT 'EDIT',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_id` (`role_id`,
    `resource`,
    `field`),
    CONSTRAINT `field_authorizations_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON
    DELETE
        CASCADE ON
        UPDATE
            CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 109 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.cgr_codes definition

CREATE TABLE `cgr_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL COMMENT 'Lien vers l''Entité propriétaire',
  `type` varchar(32) NOT NULL COMMENT 'Type de zone (SERVICE, TITRE...)',
  `code` varchar(16) NOT NULL COMMENT 'La valeur courte (ex: S01)',
  `label` varchar(255) NOT NULL COMMENT 'Libellé complet',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cgr_codes_unique_definition` (`department_id`,
`type`,
`code`),
  CONSTRAINT `fk_cgr_codes_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON
DELETE
    CASCADE ON
    UPDATE
        CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Dictionnaire des valeurs analytiques';
-- daetf.departments definition

CREATE TABLE `departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `cgr_code_id` int unsigned DEFAULT NULL,
  `lft` int DEFAULT NULL,
  `rght` int DEFAULT NULL,
  `level` int DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '1',
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `sort` varchar(64) DEFAULT '',
  `department_type_id` int NOT NULL DEFAULT '1',
  `cgr_strategy_id` int unsigned DEFAULT NULL COMMENT 'Référence à la stratégie CGR',
  `default_cgr` varchar(255) DEFAULT NULL COMMENT 'CGR par défaut pré-calculé',
  `current_manager_id` int unsigned DEFAULT NULL COMMENT 'identifiant responsable de service',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `code_2` (`code`),
    UNIQUE KEY `name_2` (`name`),
    KEY `sort` (`sort`),
    KEY `sort_2` (`sort`),
    KEY `idx_lft` (`lft`),
    KEY `parent_id` (`parent_id`),
    KEY `fk_departments_cgr_strategy_id` (`cgr_strategy_id`),
    KEY `fk_departments_cgr_codes` (`cgr_code_id`),
    CONSTRAINT `fk_departments_cgr_codes` FOREIGN KEY (`cgr_code_id`) REFERENCES `cgr_codes` (`id`) ON
    DELETE
        SET
        NULL ON
        UPDATE
            CASCADE,
            CONSTRAINT `fk_departments_cgr_strategy_id` FOREIGN KEY (`cgr_strategy_id`) REFERENCES `cgr_strategies` (`id`) ON
            DELETE
                SET
                NULL ON
                UPDATE
                    CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 73 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.applicationformstatuses source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `daetf`.`applicationformstatuses` AS
select
    `af`.`id` AS `applicationform_id`,
    (case
        when (count(`v`.`id`) > 0) then true
        else false
    end) AS `has_validations`,
    (case
        when (count(`v`.`id`) > 0) then (case
            when (max(`v`.`validationstatus_id`) = 6) then 6
            when (max(`v`.`validationstatus_id`) = 5) then 5
            when ((min(`v`.`validationstatus_id`) >= 3)
            and (max(`v`.`validationstatus_id`) <= 4)) then 4
            when ((max(`v`.`validationstatus_id`) < 6)
            and (sum((case when (`v`.`validationstatus_id` = 2) then 1 else 0 end)) > 0)) then 2
            else 1
        end)
        else 1
    end) AS `validationstatus_id`,
    (case
        when (count(`v`.`id`) > 0) then round(((sum((case when (`v`.`validationstatus_id` > 2) then 1 else 0 end)) / count(`v`.`id`)) * 100), 2)
        else 0
    end) AS `valid_percentage`,
    (case
        when (count(`vs`.`id`) > 0) then min(`vs`.`sequence`)
        else NULL
    end) AS `current_sequence`,
    (case
        when ((count(`v`.`id`) > 0)
        and (max(`v`.`validationstatus_id`) <> 2)) then true
        else false
    end) AS `en_cours`,
    (case
        when ((count(`v`.`id`) > 0)
        and (min(`v`.`validationstatus_id`) in (3, 4))
        and (max(`v`.`validationstatus_id`) in (3, 4))) then true
        else false
    end) AS `accepted`,
    (case
        when ((count(`v`.`id`) > 0)
        and (max(`v`.`validationstatus_id`) in (5, 6))) then true
        else false
    end) AS `rejected`
from
    ((`daetf`.`applicationforms` `af`
left join `daetf`.`validations` `v` on
    ((`af`.`id` = `v`.`applicationform_id`)))
left join `daetf`.`validationsequences` `vs` on
    (((`af`.`department_id` = `vs`.`department_id`)
        and `vs`.`role_id` in (
        select
            `v1`.`role_id`
        from
            `daetf`.`validations` `v1`
        where
            ((`v1`.`applicationform_id` = `af`.`id`)
                and (`v1`.`validationstatus_id` in (4, 3)))) is false)))
group by
    `af`.`id`;
-- daetf.currentvalidationroles source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `daetf`.`currentvalidationroles` AS
select
    `a`.`id` AS `applicationform_id`,
    `a`.`department_id` AS `department_id`,
    `vs`.`role_id` AS `validator_role_id`,
    `vs`.`sequence` AS `validation_sequence`,
    `v`.`validationstatus_id` AS `validationstatus_id`,
    `daetf`.`a2`.`en_cours` AS `en_cours`,
    `daetf`.`a2`.`accepted` AS `accepted`,
    `daetf`.`a2`.`rejected` AS `rejected`
from
    (((`daetf`.`applicationforms` `a`
join `daetf`.`validationsequences` `vs` on
    ((`vs`.`department_id` = `a`.`department_id`)))
join `daetf`.`applicationformstatuses` `a2` on
    (((0 <> `daetf`.`a2`.`has_validations`)
        and (`a`.`id` = `daetf`.`a2`.`applicationform_id`)
            and (`vs`.`sequence` = `daetf`.`a2`.`current_sequence`))))
left join `daetf`.`validations` `v` on
    (((`v`.`applicationform_id` = `a`.`id`)
        and (`v`.`role_id` = `vs`.`role_id`))));
-- daetf.urds source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `daetf`.`urds` AS
select
    `ud`.`user_id` AS `user_id`,
    `u`.`role_id` AS `role_id`,
    `ud`.`department_id` AS `department_id`
from
    (`daetf`.`user_departments` `ud`
join `daetf`.`users` `u` on
    ((`u`.`id` = `ud`.`user_id`)));
-- daetf.validation_visas source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `daetf`.`validation_visas` AS
select
    `af`.`id` AS `applicationform_id`,
    `vs`.`sequence` AS `sequence`,
    `vs`.`role_id` AS `role_id`,
    (case
        when (`v`.`validationstatus_id` = 2) then ''
        else concat(`u`.`firstname`, ' ', `u`.`lastname`)
    end) AS `op_name`,
    `r`.`name` AS `role_name`,
    `s`.`name` AS `status_name`,
    `v`.`modified` AS `validated_at`
from
    (((((`daetf`.`applicationforms` `af`
join `daetf`.`validationsequences` `vs` on
    ((`vs`.`department_id` = `af`.`department_id`)))
join `daetf`.`roles` `r` on
    ((`r`.`id` = `vs`.`role_id`)))
left join `daetf`.`validations` `v` on
    (((`v`.`applicationform_id` = `af`.`id`)
        and (`v`.`role_id` = `vs`.`role_id`))))
left join `daetf`.`users` `u` on
    ((`v`.`user_id` = `u`.`id`)))
left join `daetf`.`validationstatuses` `s` on
    ((`s`.`id` = `v`.`validationstatus_id`)))
where
    ((`af`.`deleted` is null)
        and (`v`.`created` is not null))
order by
    `af`.`id`,
    `vs`.`sequence`;


