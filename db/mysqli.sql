CREATE TABLE ishikawa (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    course BIGINT(10) UNSIGNED NOT NULL,
    name  VARCHAR(255) NOT NULL,
    description TEXT,
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
    timecreated BIGINT(10) UNSIGNED NOT NULL,
    timemodified BIGINT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX uni_ishisub_ishiuser (ishikawaid, userid)
);

CREATE TABLE ishikawa_blocks (
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
