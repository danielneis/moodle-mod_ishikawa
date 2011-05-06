CREATE TABLE ishikawa (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    course BIGINT(10) UNSIGNED NOT NULL,
    name  VARCHAR(255) NOT NULL,
    description TEXT,
    maxchar BIGINT(10) UNSIGNED NOT NULL,
    maxlines BIGINT(10) UNSIGNED NOT NULL,
    maxcolumns BIGINT(10) UNSIGNED NOT NULL,
    grade bigint(10) NOT NULL DEFAULT 0,
    timedue BIGINT(10) UNSIGNED NOT NULL,
    timeavailable BIGINT(10) UNSIGNED NOT NULL,
    timemodified BIGINT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY ishi_cou_ix (course)
);

CREATE TABLE ishikawa_submissions (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    ishikawaid BIGINT(10) UNSIGNED NOT NULL,
    userid BIGINT(10) UNSIGNED NOT NULL,
    tail_text TEXT NOT NULL,
    head_text TEXT NOT NULL,
    timecreated BIGINT(10) UNSIGNED NOT NULL,
    timemodified BIGINT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX uni_ishisub_ishiuser (ishikawaid, userid)
);

CREATE TABLE ishikawa_grades (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    ishikawaid BIGINT(10) UNSIGNED NOT NULL,
    userid BIGINT(10) UNSIGNED NOT NULL,
    grade BIGINT(11) NOT NULL DEFAULT 0,
    feedback TEXT,
    timecreated BIGINT(10) UNSIGNED NOT NULL,
    timemodified BIGINT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX uni_ishigrade_ishiuser (ishikawaid, userid)
);

CREATE TABLE ishikawa_causes_blocks (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    submissionid BIGINT(10) NOT NULL,
    nivel_x BIGINT(10) NOT NULL,
    nivel_y BIGINT(10) NOT NULL,
    texto TEXT NOT NULL,
    cor VARCHAR(255),
    PRIMARY KEY (id),
    UNIQUE INDEX uniq_ishicausesblocks_subniveis (submissionid, nivel_x, nivel_y),
    INDEX ishiblocks_sub_ix (submissionid)
);

CREATE TABLE ishikawa_axis_blocks (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    submissionid BIGINT(10) NOT NULL,
    nivel_x BIGINT(10) NOT NULL,
    texto TEXT NOT NULL,
    cor VARCHAR(255),
    PRIMARY KEY (id),
    UNIQUE INDEX uniq_ishiblocks_subniveis (submissionid, nivel_x),
    INDEX ishiblocks_sub_ix (submissionid)
);

CREATE TABLE ishikawa_consequences_blocks (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    submissionid BIGINT(10) NOT NULL,
    nivel_x BIGINT(10) NOT NULL,
    nivel_y BIGINT(10) NOT NULL,
    texto TEXT NOT NULL,
    cor VARCHAR(255),
    PRIMARY KEY (id),
    UNIQUE INDEX uniq_ishiblocks_subniveis (submissionid, nivel_x, nivel_y),
    INDEX ishiblocks_sub_ix (submissionid)
);

CREATE TABLE ishikawa_connections (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    submissionid BIGINT(10) NOT NULL,
    src_id BIGINT(10) UNSIGNED NOT NULL,
    src_type ENUM('causes', 'axis', 'consequences') NOT NULL,
    dst_id BIGINT(10) UNSIGNED NOT NULL,
    dst_type ENUM('causes', 'axis', 'consequences') NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX uniq_ishiconsequencesblocks_srcdst (src_id, src_type, dst_id, dst_type)
);
