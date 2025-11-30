-- Migration: Create dosen_mahasiswa relationship table
-- This allows admin to assign students to teachers
-- Run this SQL query in your phpMyAdmin or MySQL client

CREATE TABLE IF NOT EXISTS dosen_mahasiswa_231051 (
    id_dm_231051 INT AUTO_INCREMENT PRIMARY KEY,
    id_dosen_231051 INT NOT NULL,
    id_mahasiswa_231051 INT NOT NULL,
    tanggal_assign_231051 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dosen_231051) REFERENCES dosen_231051(id_dosen_231051) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa_231051) REFERENCES mahasiswa_231051(id_mahasiswa_231051) ON DELETE CASCADE,
    UNIQUE KEY unique_dosen_mahasiswa (id_dosen_231051, id_mahasiswa_231051)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Table for assigning students to teachers';
