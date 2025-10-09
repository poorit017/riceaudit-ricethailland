CREATE TABLE IF NOT EXISTS memberinfo (
    Memberinfo_id INT AUTO_INCREMENT PRIMARY KEY,
    Memberinfo_fname VARCHAR(100),
    Memberinfo_lname VARCHAR(100),
    Memberinfo_agency VARCHAR(255),
    Memberinfo_tel VARCHAR(50),
    Memberinfo_pos VARCHAR(100),
    Memberinfo_typepos VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS member (
    Member_id INT AUTO_INCREMENT PRIMARY KEY,
    Member_titlename VARCHAR(50),
    Member_firstname VARCHAR(100),
    Member_lastname VARCHAR(100),
    Member_agency VARCHAR(100),
    Member_affiliation VARCHAR(255),
    Member_course VARCHAR(100),
    Member_time VARCHAR(50),
    Member_year VARCHAR(4),
    Member_certificate VARCHAR(255),
    ID_Member INT,
    FOREIGN KEY (ID_Member) REFERENCES memberinfo(Memberinfo_id)
);
