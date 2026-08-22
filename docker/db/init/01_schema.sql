CREATE TABLE tag(
    id int NOT NULL AUTO_INCREMENT,
    name varchar(20) NOT NULL,
    color varchar(7) NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE task(
    id int NOT NULL AUTO_INCREMENT,
    title varchar(50) NOT NULL,
    start_date date NULL,
    deadline date NULL,
    tag_id int NULL,
    memo varchar(255) NULL,
    done tinyint(1) NOT NULL DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sub_task(
    id int NOT NULL AUTO_INCREMENT,
    task_id int NOT NULL,
    title varchar(50) NOT NULL,
    done tinyint(1) NOT NULL DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (task_id) REFERENCES task(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;