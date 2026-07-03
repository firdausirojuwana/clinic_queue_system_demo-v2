-- ============================================
--  CLINIC QUEUE MANAGEMENT SYSTEM
--  IMD261 Group Assignment - KCDIM1104B
--  Universiti Teknologi MARA Sungai Petani
-- ============================================

DROP DATABASE IF EXISTS clinic_queue;
CREATE DATABASE clinic_queue;
USE clinic_queue;

-- ============================================
-- TABLE 1: PATIENT
-- ============================================
CREATE TABLE patient (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ic_number VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20),
    email VARCHAR(100),
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 2: DOCTOR
-- ============================================
CREATE TABLE doctor (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) DEFAULT 'General Practice',
    phone VARCHAR(20),
    room_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 3: APPOINTMENT
-- ============================================
CREATE TABLE appointment (
    appoint_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    queue_number INT NOT NULL,
    appointment_datetime DATETIME NOT NULL,
    status ENUM('Waiting', 'In Consultation', 'Completed', 'Cancelled') DEFAULT 'Waiting',
    symptoms TEXT,
    diagnosis TEXT,
    blood_pressure VARCHAR(20),
    temperature DECIMAL(4,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ============================================
-- TABLE 4: MEDICINE
-- ============================================
CREATE TABLE medicine (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50),
    unit_price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 5: PRESCRIPTION
-- ============================================
CREATE TABLE prescription (
    prescript_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    appoint_id INT NOT NULL,
    presc_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_cost DECIMAL(8,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (appoint_id) REFERENCES appointment(appoint_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================
-- TABLE 6: PRESCRIPTION_MEDICINE
-- ============================================
CREATE TABLE prescription_medicine (
    pm_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (prescription_id) REFERENCES prescription(prescript_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicine(medicine_id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ============================================
-- SAMPLE DATA
-- ============================================

INSERT INTO patient (name, ic_number, phone, email, gender, address) VALUES
('Adnan', '020411-02-0197', '017-5650624', 'adnan12@gmail.com', 'Male', 'No. 12, Jalan Mawar, Sungai Petani, Kedah'),
('Siti Aisyah', '010523-08-1234', '011-2345678', 'sitiaisyah@gmail.com', 'Female', 'No. 5, Jalan Kenanga, Alor Setar, Kedah'),
('Hafiz', '990314-04-5678', '019-8765432', 'hafiz99@gmail.com', 'Male', 'No. 88, Jalan Dahlia, Kulim, Kedah');

INSERT INTO doctor (name, specialty, phone, room_number) VALUES
('Dr. Farhaan', 'General Practice', '04-4412000', 'Room 1'),
('Dr. Nurul Ain', 'General Practice', '04-4412001', 'Room 2'),
('Dr. Zulkifli', 'General Practice', '04-4412002', 'Room 3');

INSERT INTO medicine (medicine_name, dosage, unit_price, stock_quantity) VALUES
('Panadol', '500mg', 2.00, 100),
('Vitamin C', '1000mg', 5.00, 50),
('Clarinase', '5mg', 3.50, 80),
('Strepsils', '8.75mg', 4.00, 60),
('Augmentin', '625mg', 8.00, 40),
('Ibuprofen', '400mg', 2.50, 90);

-- ============================================
-- INDEXES
-- ============================================
CREATE INDEX idx_appointment_date ON appointment(appointment_datetime);
CREATE INDEX idx_patient_name ON patient(name);
CREATE INDEX idx_medicine_name ON medicine(medicine_name);

-- ============================================
-- TRIGGER: AUTO REDUCE STOCK
-- ============================================
DELIMITER //
CREATE TRIGGER reduce_medicine_stock
AFTER INSERT ON prescription_medicine
FOR EACH ROW
BEGIN
    UPDATE medicine
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE medicine_id = NEW.medicine_id;
END//
DELIMITER ;