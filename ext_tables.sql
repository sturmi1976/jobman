CREATE TABLE tx_jobman_domain_model_job (
    uid INT(11) NOT NULL auto_increment,
    pid INT(11) DEFAULT 0 NOT NULL,

    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT '' NOT NULL,
    description TEXT,
    location VARCHAR(255) DEFAULT '' NOT NULL,
    employment_type VARCHAR(50) DEFAULT '' NOT NULL,
    salary VARCHAR(255) DEFAULT '' NOT NULL,
    remote TINYINT(1) DEFAULT 0 NOT NULL,
    remote_type VARCHAR(30) DEFAULT '' NOT NULL,
    remote_custom_text varchar(255) DEFAULT '' NOT NULL,
    valid_through INT(11) DEFAULT 0 NOT NULL,
    views INT(11) DEFAULT '0' NOT NULL,

    tstamp INT(11) DEFAULT 0 NOT NULL,
    crdate INT(11) DEFAULT 0 NOT NULL,
    cruser_id INT(11) DEFAULT 0 NOT NULL,
    deleted TINYINT(1) DEFAULT 0 NOT NULL,
    hidden TINYINT(1) DEFAULT 0 NOT NULL,
    starttime INT(11) DEFAULT 0 NOT NULL,
    endtime INT(11) DEFAULT 0 NOT NULL,
    sorting INT(11) DEFAULT 0 NOT NULL,

    sys_language_uid INT(11) DEFAULT 0 NOT NULL,
    l10n_parent INT(11) DEFAULT 0 NOT NULL,
    l10n_diffsource MEDIUMBLOB,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_jobman_job_views (
    uid int(11) NOT NULL AUTO_INCREMENT,

    job int(11) NOT NULL,

    -- DSGVO freundlich: IP wird gehashed gespeichert
    ip_hash varchar(64) NOT NULL,

    -- Zeitfenster Bucket (z.B. 24h Fenster)
    bucket int(11) NOT NULL,

    tstamp int(11) NOT NULL,

    PRIMARY KEY (uid),

    -- Ein View pro Job pro IP pro Zeitfenster
    UNIQUE KEY unique_view (job, ip_hash, bucket),

    KEY job_index (job),
    KEY bucket_index (bucket)
);



CREATE TABLE tx_jobman_domain_model_application (
    uid INT(11) NOT NULL auto_increment,
    pid INT(11) DEFAULT 0 NOT NULL,

    job INT(11) DEFAULT 0 NOT NULL,
    name VARCHAR(255) DEFAULT '' NOT NULL,
    email VARCHAR(255) DEFAULT '' NOT NULL,
    message TEXT,

    tstamp INT(11) DEFAULT 0 NOT NULL,
    crdate int(11) DEFAULT 0 NOT NULL,
    date int(11) DEFAULT 0 NOT NULL,
    deleted TINYINT(1) DEFAULT 0 NOT NULL,
    hidden TINYINT(1) DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);


CREATE TABLE tx_jobman_domain_model_application (
    status int(11) DEFAULT '0' NOT NULL
);
