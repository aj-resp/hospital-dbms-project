-- =============================================
-- ivor paine memorial hospital
-- milestone 2 —  database systems lab
-- kasim zeeshan alvi   24i0549
-- abdullah junaid      24i0569
-- muhammad sarim       24i0668
-- =============================================

use master
go

if not exists (select name from sys.databases where name = 'IvorPaineHospital')
    create database IvorPaineHospital
go

use IvorPaineHospital
go


--Drop tables (children before parents)

if object_id('grades','U') is not null drop table grades
if object_id('belongs_to_team','U') is not null drop table belongs_to_team
if object_id('previous_experience','U') is not null drop table previous_experience
if object_id('Treatment','U') is not null drop table Treatment
if object_id('complaint','U') is not null drop table complaint
if object_id('Assigned_To','U') is not null drop table Assigned_To
if object_id('patient','U') is not null drop table patient
if object_id('non_registered_nurse', 'U') is not null drop table non_registered_nurse
if object_id('staff_nurse','U') is not null drop table staff_nurse
if object_id('night_sister','U') is not null drop table night_sister
if object_id('day_sister','U') is not null drop table day_sister
if object_id('nurse','U') is not null drop table nurse
if object_id('consultant','U') is not null drop table consultant
if object_id('doctor','U') is not null drop table doctor
if object_id('care_unit','U') is not null drop table care_unit
if object_id('bed','U') is not null drop table bed
if object_id('ward','U') is not null drop table ward
if object_id('specialty','U') is not null drop table specialty
go


-- Creating tables

-- lookup table for medical specialties
create table specialty (
    SpecialtyName nvarchar(50)  not null,
    Description nvarchar(200) null,
    constraint PK_specialty primary key (SpecialtyName)
)
go

-- each ward caters to one specialty; many wards can share a specialty
create table ward (
    WardName nvarchar(50)  not null,
    Location nvarchar(100) not null,
    Capacity int not null,
    SpecialtyName nvarchar(50)  not null,
    constraint PK_ward primary key (WardName),
    constraint CK_ward_cap  check (Capacity > 0),
    constraint FK_ward_spec foreign key (SpecialtyName)
        references specialty(SpecialtyName)
        on update cascade
        on delete no action
)
go

-- each bed belongs to one ward and status defaults to available
create table bed (
    BedNo int not null,
    WardName nvarchar(50) not null,
    Status nvarchar(20) not null
             constraint DF_bed_status  default 'Available'
             constraint CK_bed_status  check (Status in ('Available', 'Occupied', 'Under Maintenance')),
    constraint PK_bed primary key (BedNo),
    constraint FK_bed_ward foreign key (WardName)
        references ward(WardName)
        on update cascade
        on delete no action
)
go

--care units
create table care_unit (
    CareUnitNo int not null identity(1,1),
    WardName nvarchar(50) not null,
    constraint PK_care_unit primary key (CareUnitNo),
    constraint FK_cu_ward   foreign key (WardName)
        references ward(WardName)
        on update cascade
        on delete no action
)
go

-- all doctors including consultants position is restricted by check constraint
create table doctor (
    StaffNo int not null,
    Name nvarchar(100) not null,
    Position nvarchar(50)  not null,
    Phone nvarchar(20)  null,
    constraint PK_doctor primary key (StaffNo),
    constraint CK_doctor_pos check (Position in (
        'Student', 'Junior Houseman', 'Senior Houseman',
        'Assistant Registrar', 'Registrar', 'Consultant'
    ))
)
go

-- consultant is a subtype of doctor
create table consultant (
    StaffNo int not null,
    SpecialtyName nvarchar(50) not null,
    constraint PK_consultant primary key (StaffNo),
    constraint FK_cons_doctor foreign key (StaffNo)
        references doctor(StaffNo)
        on update cascade
        on delete cascade,
    constraint FK_cons_specialty foreign key (SpecialtyName)
        references specialty(SpecialtyName)
        on update cascade
        on delete no action
)
go

-- each nurse works on one ward and is allocated to exactly one care unit
create table nurse (
    StaffNo int           not null,
    Name nvarchar(100) not null,
    Phone nvarchar(20)  null,
    WardName nvarchar(50)  not null,
    CareUnitNo int not null,
    constraint PK_nurse primary key (StaffNo),
    constraint FK_nurse_ward foreign key (WardName)
        references ward(WardName)
        on update cascade
        on delete no action,
    constraint FK_nurse_cu foreign key (CareUnitNo)
        references care_unit(CareUnitNo)
        on update no action
        on delete no action
)
go

-- isa subtype: day shift ward nurse
create table day_sister (
    StaffNo int not null,
    constraint PK_day_sister primary key (StaffNo),
    constraint FK_ds_nurse   foreign key (StaffNo)
        references nurse(StaffNo)
        on update cascade
        on delete cascade
)
go

-- night shift ward nurse
create table night_sister (
    StaffNo int not null,
    constraint PK_night_sister primary key (StaffNo),
    constraint FK_ns_nurse foreign key (StaffNo)
        references nurse(StaffNo)
        on update cascade
        on delete cascade
)
go

-- registered nurse in charge of a specific care unit
create table staff_nurse (
    StaffNo int not null,
    CareUnitNo int not null,
    constraint PK_staff_nurse primary key (StaffNo),
    constraint FK_sn_nurse foreign key (StaffNo)
        references nurse(StaffNo)
        on update cascade
        on delete cascade,
    constraint FK_sn_cu foreign key (CareUnitNo)
        references care_unit(CareUnitNo)
        on update no action
        on delete no action
)
go

-- nursing staff without formal registration
create table non_registered_nurse (
    StaffNo int not null,
    constraint PK_nrn primary key (StaffNo),
    constraint FK_nrn_nurse foreign key (StaffNo)
        references nurse(StaffNo)
        on update cascade
        on delete cascade
)
go

-- patient belongs to one ward, bed, doctor and care unit at a time
create table patient (
    PatientNo int not null,
    Name nvarchar(100) not null,
    DateOfBirth date  not null,
    Address nvarchar(200) null,
    Phone nvarchar(20) null,
    WardName nvarchar(50) not null,
    BedNo int  not null,
    DoctorStaffNo int not null,
    DateAdmitted date not null,
    CareUnitNo int not null,
    constraint PK_patient primary key (PatientNo),
    constraint FK_pat_ward foreign key (WardName)
        references ward(WardName)
        on update cascade
        on delete no action,
    constraint FK_pat_bed foreign key (BedNo)
        references bed(BedNo)
        on update no action
        on delete no action,
    constraint FK_pat_doctor foreign key (DoctorStaffNo)
        references doctor(StaffNo)
        on update no action
        on delete no action,
    constraint FK_pat_cu foreign key (CareUnitNo)
        references care_unit(CareUnitNo)
        on update no action
        on delete no action
)
go

-- tracks patient ward assignment history composite pk allows multiple admissions
create table Assigned_To (
    DateAdmitted date  not null,
    PatientNo int not null,
    WardName nvarchar(50) not null,
    constraint PK_assigned  primary key (PatientNo, WardName),
    constraint FK_at_patient foreign key (PatientNo)
        references patient(PatientNo)
        on update cascade
        on delete cascade,
    constraint FK_at_ward foreign key (WardName)
        references ward(WardName)
        on update no action   -- no action avoids multiple cascade paths ward->patient->Assigned_To and ward->Assigned_To
        on delete no action
)
go

-- a patient can have multiple complaints at the same time
create table complaint (
    ComplaintID int not null identity(1,1),
    PatientNo   int not null,
    Description nvarchar(300) not null,
    constraint PK_complaint primary key (ComplaintID),
    constraint FK_comp_patient foreign key (PatientNo)
        references patient(PatientNo)
        on update cascade
        on delete cascade
)
go

-- Treatment Table

create table Treatment (
    TreatmentID int not null identity(1,1),
    TreatmentDate date not null,
    ComplaintID int not null,
    DoctorStaffNo int not null,
    TreatmentType nvarchar(100) not null,
    TreatmentEnd date null,
    constraint PK_treatment primary key (TreatmentID),
    constraint FK_treat_comp foreign key (ComplaintID)
        references complaint(ComplaintID)
        on update cascade
        on delete no action,
    constraint FK_treat_doctor foreign key (DoctorStaffNo)
        references doctor(StaffNo)
        on update no action
        on delete no action,
    constraint CK_treat_dates check (TreatmentEnd is null or TreatmentEnd >= TreatmentDate)
)
go

-- bridge table resolving m:n between doctor and consultant over time
create table belongs_to_team (
    DoctorStaffNo int  not null,
    ConsultantStaffNo int  not null,
    DateJoinedTeam date not null,
    DateLeaveTeam date null,
    constraint PK_btt primary key (DoctorStaffNo, ConsultantStaffNo, DateJoinedTeam),
    constraint FK_btt_doctor foreign key (DoctorStaffNo)
        references doctor(StaffNo)
        on update no action
        on delete no action,
    constraint FK_btt_cons foreign key (ConsultantStaffNo)
        references consultant(StaffNo)
        on update no action
        on delete no action,
    constraint CK_btt_dates  check (DateLeaveTeam is null or DateLeaveTeam >= DateJoinedTeam)
)
go

create table grades (
    ConsultantStaffNo int not null,
    DoctorStaffNo int not null,
    ReviewDate date not null,
    Grade nvarchar(5) not null,
    constraint PK_grades primary key (ConsultantStaffNo, DoctorStaffNo, ReviewDate),
    constraint FK_gr_consultant foreign key (ConsultantStaffNo)
        references consultant(StaffNo)
        on update no action
        on delete no action,
    constraint FK_gr_doctor foreign key (DoctorStaffNo)
        references doctor(StaffNo)
        on update no action
        on delete no action,
    constraint CK_grade check (Grade in ('A', 'B', 'C', 'D', 'F'))
)
go

-- stores prior employment history for each doctor
create table previous_experience (
    DoctorStaffNo int not null,
    FromDate date not null,
    ToDate date null,
    Position nvarchar(50)  not null,
    Establishment nvarchar(100) not null,
    constraint PK_prev_exp primary key (DoctorStaffNo, FromDate),
    constraint FK_pe_doctor foreign key (DoctorStaffNo)
        references doctor(StaffNo)
        on update cascade
        on delete cascade,
    constraint CK_pe_dates check (ToDate is null or ToDate >= FromDate)
)
go

print 'all tables created successfully'
go


-- Data insertion

-- specialties 
insert into specialty (SpecialtyName, Description) values
('Orthopedic',       'Diagnosis and surgical treatment of musculoskeletal disorders including fractures and joint disease'),
('Geriatric',        'Comprehensive medical care for elderly patients with complex multi-system conditions'),
('Cardiology',       'Diagnosis and management of cardiovascular diseases including heart failure and arrhythmia'),
('Neurology',        'Treatment of disorders of the nervous system including stroke, epilepsy and neuropathy'),
('Oncology',         'Medical management of malignant tumours through chemotherapy, radiotherapy and supportive care'),
('Pediatrics',       'General and specialist medical care for infants, children and adolescents'),
('Dermatology',      'Diagnosis and treatment of skin, hair and nail conditions including inflammatory and infectious disease'),
('Psychiatry',       'Assessment and treatment of mental health disorders including mood, anxiety and psychotic conditions'),
('Gastroenterology', 'Investigation and management of digestive system disorders from oesophagus to colon'),
('Pulmonology',      'Diagnosis and treatment of respiratory tract and lung diseases including COPD and pneumonia')
go


-- wards 
insert into ward (WardName, Location, Capacity, SpecialtyName) values
('Orthopaedic Ward A',    'Block 1 Floor 1', 20, 'Orthopedic'),
('Geriatric Ward',  'Block 1 Floor 2', 15, 'Geriatric'),
('Cardiology Ward A',      'Block 2 Floor 1', 18, 'Cardiology'),
('Neurology Ward',        'Block 2 Floor 2', 12, 'Neurology'),
('Oncology Ward',  'Block 3 Floor 1', 20, 'Oncology'),
('Paediatric Ward',  'Block 3 Floor 2', 16, 'Pediatrics'),
('Dermatology Ward',  'Block 4 Floor 1', 14, 'Dermatology'),
('Psychiatric Ward',    'Block 4 Floor 2', 10, 'Psychiatry'),
('Gastroenterology Ward',   'Block 5 Floor 1', 18, 'Gastroenterology'),
('Pulmonology Ward', 'Block 5 Floor 2', 15, 'Pulmonology'),
('Cardiology Ward B',       'Block 6 Floor 1', 20, 'Cardiology'),
('Orthopaedic Ward B',      'Block 6 Floor 2', 12, 'Orthopedic')
go


-- beds 
insert into bed (BedNo, WardName, Status) values
(101, 'Orthopaedic Ward A',    'Occupied'),
(102, 'Orthopaedic Ward A',    'Occupied'),
(103, 'Orthopaedic Ward A',    'Available'),
(104, 'Orthopaedic Ward A',    'Occupied'),
(105, 'Geriatric Ward',  'Occupied'),
(106, 'Geriatric Ward',  'Occupied'),
(107, 'Geriatric Ward',  'Under Maintenance'),
(108, 'Cardiology Ward A',      'Occupied'),
(109, 'Cardiology Ward A',      'Occupied'),
(110, 'Cardiology Ward A',      'Available'),
(111, 'Neurology Ward',        'Occupied'),
(112, 'Neurology Ward',        'Occupied'),
(113, 'Oncology Ward',  'Occupied'),
(114, 'Oncology Ward',  'Occupied'),
(115, 'Oncology Ward',  'Occupied'),
(116, 'Paediatric Ward',  'Occupied'),
(117, 'Paediatric Ward',  'Available'),
(118, 'Dermatology Ward',  'Occupied'),
(119, 'Dermatology Ward',  'Occupied'),
(120, 'Psychiatric Ward',    'Occupied'),
(121, 'Psychiatric Ward',    'Available'),
(122, 'Gastroenterology Ward',   'Occupied'),
(123, 'Gastroenterology Ward',   'Occupied'),
(124, 'Pulmonology Ward', 'Occupied'),
(125, 'Pulmonology Ward', 'Occupied'),
(126, 'Cardiology Ward B',       'Occupied'),
(127, 'Cardiology Ward B',       'Occupied'),
(128, 'Cardiology Ward B',       'Occupied'),
(129, 'Orthopaedic Ward B',      'Occupied'),
(130, 'Orthopaedic Ward B',      'Available'),
(131, 'Orthopaedic Ward A',    'Available'),
(132, 'Geriatric Ward',  'Occupied'),
(133, 'Cardiology Ward A',      'Occupied'),
(134, 'Neurology Ward',        'Available'),
(135, 'Oncology Ward',  'Occupied'),
(136, 'Paediatric Ward',  'Occupied'),
(137, 'Dermatology Ward',  'Available'),
(138, 'Gastroenterology Ward',   'Occupied'),
(139, 'Pulmonology Ward', 'Occupied'),
(140, 'Cardiology Ward B',       'Under Maintenance')
go

-- care units

insert into care_unit (WardName) values
('Orthopaedic Ward A'),    -- 1
('Orthopaedic Ward A'),    -- 2
('Geriatric Ward'),  -- 3
('Cardiology Ward A'),      -- 4
('Cardiology Ward A'),      -- 5
('Neurology Ward'),        -- 6
('Oncology Ward'),  -- 7
('Paediatric Ward'),  -- 8
('Dermatology Ward'),  -- 9
('Psychiatric Ward'),    -- 10
('Gastroenterology Ward'),   -- 11
('Pulmonology Ward'), -- 12
('Cardiology Ward B'),       -- 13
('Cardiology Ward B'),       -- 14
('Orthopaedic Ward B')       -- 15
go


-- doctors

insert into doctor (StaffNo, Name, Position, Phone) values
(1001, 'Dr. Khalid Mehmood',    'Consultant',          '051-4856231'),
(1002, 'Dr. Farzana Qureshi',   'Consultant',          '0300-8214567'),
(1003, 'Dr. Imran Baig',        'Consultant',          '0321-5039871'),
(1004, 'Dr. Nadia Hassan',      'Consultant',          '0333-7124896'),
(1005, 'Dr. Tariq Hussain',     'Registrar',           '0311-4523678'),
(1006, 'Dr. Saba Malik',        'Assistant Registrar', '0345-9017234'),
(1007, 'Dr. Asif Rehman',       'Senior Houseman',     '051-2873645'),
(1008, 'Dr. Farah Mirza',       'Junior Houseman',     '0321-6748392'),
(1009, 'Dr. Zubair Khan',       'Junior Houseman',     null),
(1010, 'Dr. Hira Chaudhry',     'Registrar',           '0300-5512984'),
(1011, 'Dr. Usman Siddiqui',    'Senior Houseman',     '0311-8834761'),
(1012, 'Dr. Amna Farooq',       'Assistant Registrar', '0333-2461057'),
(1013, 'Dr. Bilal Sheikh',      'Junior Houseman',     '0345-7093412'),
(1014, 'Dr. Rida Akhtar',       'Student',             null)
go

-- consultant
insert into consultant (StaffNo, SpecialtyName) values
(1001, 'Orthopedic'),
(1002, 'Cardiology'),
(1003, 'Neurology'),
(1004, 'Oncology')
go

-- nurses

insert into nurse (StaffNo, Name, Phone, WardName, CareUnitNo) values
(2001, 'Nurse Ayesha Noor',        '0321-4512367', 'Orthopaedic Ward A',     1),
(2002, 'Nurse Sara Qureshi',       '0311-7834521', 'Orthopaedic Ward A',     2),
(2003, 'Nurse Maria Hassan',       '0300-6129034', 'Geriatric Ward',   3),
(2004, 'Nurse Hina Javed',         '0333-8740156', 'Cardiology Ward A',       4),
(2005, 'Nurse Rabia Farooq',       null,           'Cardiology Ward A',       5),
(2006, 'Nurse Zara Sheikh',        '0345-2093817', 'Neurology Ward',         6),
(2007, 'Nurse Nadia Butt',         '0321-9345602', 'Oncology Ward',   7),
(2008, 'Nurse Sana Iqbal',         '0311-5628743', 'Paediatric Ward',   8),
(2009, 'Nurse Amna Rizvi',         '0300-4178265', 'Dermatology Ward',   9),
(2010, 'Nurse Fatima Malik',       null,           'Psychiatric Ward',    10),
(2011, 'Nurse Kiran Baig',         '0333-6091452', 'Gastroenterology Ward',   11),
(2012, 'Nurse Mehwish Chaudhry',   '0345-8234709', 'Pulmonology Ward', 12),
(2013, 'Nurse Tahira Ansari',      '0321-1047836', 'Cardiology Ward B',       13),
(2014, 'Nurse Bushra Memon',       '0311-3692581', 'Cardiology Ward B',       14),
(2015, 'Nurse Sidra Gillani',      '0300-7823490', 'Orthopaedic Ward B',      15),
(2016, 'Nurse Umber Yousaf',       '0333-5410927', 'Orthopaedic Ward A',     1),
(2017, 'Nurse Parveen Akhtar',     null,           'Geriatric Ward',   3),
(2018, 'Nurse Gulnaz Shahid',      '0321-2865034', 'Cardiology Ward A',       4),
(2019, 'Nurse Najma Rafiq',        '0311-9047163', 'Oncology Ward',   7),
(2020, 'Nurse Samina Latif',       '0345-4781320', 'Pulmonology Ward', 12)
go

insert into day_sister (StaffNo) values
(2001), (2004), (2007), (2010), (2013), (2015)
go

insert into night_sister (StaffNo) values
(2002), (2005), (2008), (2011), (2014), (2020)
go

insert into staff_nurse (StaffNo, CareUnitNo) values
(2003,  3),
(2006,  6),
(2009,  9),
(2012, 12),
(2016,  1),
(2018,  4)
go

insert into non_registered_nurse (StaffNo) values
(2017),
(2019)
go


-- Patients
insert into patient (PatientNo, Name, DateOfBirth, Address, Phone, WardName, BedNo, DoctorStaffNo, DateAdmitted, CareUnitNo) values
(3001, 'Ahmed Raza',           '1992-07-15', 'House 15, Street 4, F-7/2, Islamabad',           '0311-4523001', 'Orthopaedic Ward A',    101, 1005, '2025-01-05',  1),
(3002, 'Usman Khan',           '1999-10-10', 'Flat 7B, Safari Heights, G-11/3, Islamabad',     '0321-9870342', 'Orthopaedic Ward A',    102, 1006, '2025-01-10',  1),
(3003, 'Bilal Malik',          '1998-05-05', 'House 3, Street 12, DHA Phase 2, Lahore',        null,           'Geriatric Ward',  105, 1007, '2025-01-15',  3),
(3004, 'Abdul Qadir',          '1930-04-09', 'House 9, Gulberg III, Lahore',                   '0300-5671234', 'Geriatric Ward',  106, 1008, '2025-01-20',  3),
(3005, 'Hamza Sheikh',         '1993-07-23', 'House 22, Street 8, E-11/2, Islamabad',          '0333-8902345', 'Cardiology Ward A',      108, 1005, '2025-02-01',  4),
(3006, 'Zain ul Abideen',      '1990-01-01', 'Flat 101, Al-Habib Tower, Clifton, Karachi',     '0345-1234567', 'Cardiology Ward A',      109, 1006, '2025-02-05',  4),
(3007, 'Faisal Qureshi',       '1978-09-15', 'House 44, Street 6, G-9/4, Islamabad',           '0311-7654321', 'Neurology Ward',        111, 1007, '2025-02-10',  6),
(3008, 'Tariq Mehmood',        '1995-01-19', 'House 8, Satellite Town, Rawalpindi',            null,           'Neurology Ward',        112, 1008, '2025-02-15',  6),
(3009, 'Omar Farooq',          '1997-07-07', 'House 12, Model Town Extension, Lahore',         '0321-3456789', 'Oncology Ward',  113, 1009, '2025-03-01',  7),
(3010, 'Fatima Malik',         '1996-10-27', 'House 5, Street 3, F-8/4, Islamabad',            '0300-9876543', 'Oncology Ward',  114, 1010, '2025-03-05',  7),
(3011, 'Asad Javed',           '1980-12-25', 'House 4, Block C, PECHS, Karachi',               '0333-2345678', 'Oncology Ward',  115, 1011, '2025-03-10',  7),
(3012, 'Borhan Uzair',         '2012-03-27', 'House 7, Street 11, I-8/1, Islamabad',           '0311-5432198', 'Paediatric Ward',  116, 1012, '2025-03-15',  8),
(3013, 'Kamran Akhtar',        '2009-10-04', 'House 5, Johar Town, Lahore',                    '0345-8901234', 'Paediatric Ward',  136, 1013, '2025-03-20',  8),
(3014, 'Ayesha Khan',          '1989-01-14', 'Flat 6, Rose Apartments, F-6/1, Islamabad',      '0321-6543210', 'Dermatology Ward',  118, 1005, '2025-04-01',  9),
(3015, 'Saad Butt',            '1991-12-20', 'House 10, Street 2, Chaklala Scheme III, Rwp',   '0300-1234567', 'Dermatology Ward',  119, 1006, '2025-04-05',  9),
(3016, 'Muhammad Yousaf',      '1935-01-01', 'House 1, Street 9, G-6/3, Islamabad',            '0311-8765432', 'Psychiatric Ward',    120, 1007, '2025-04-10', 10),
(3017, 'Waqar Hussain',        '1997-11-11', 'House 5, Street 7, Defence Phase 5, Lahore',     null,           'Gastroenterology Ward',   122, 1008, '2025-04-15', 11),
(3018, 'Imran Baig',           '2001-02-03', 'House 3, Block H, North Nazimabad, Karachi',     '0333-7890123', 'Gastroenterology Ward',   123, 1009, '2025-04-20', 11),
(3019, 'Junaid Iqbal',         '2003-05-05', 'Flat 2, Gul Residency, Hayatabad, Peshawar',     '0345-4567890', 'Pulmonology Ward', 124, 1010, '2025-05-01', 12),
(3020, 'Arslan Mirza',         '2003-07-07', 'House 1, Street 5, Bahria Town Phase 4, Rwp',    '0321-2345678', 'Pulmonology Ward', 125, 1011, '2025-05-05', 12),
(3021, 'Naeem Baig',           '1988-04-16', 'House 439, Street 3, Gulshan Iqbal, Karachi',    '0311-6789012', 'Cardiology Ward B',       126, 1012, '2025-05-10', 13),
(3022, 'Saqib Nawaz',          '1996-05-18', 'House 18, Street 4, Gulshan Iqbal, Karachi',     '0300-3456789', 'Cardiology Ward B',       127, 1013, '2025-05-15', 13),
(3023, 'Shahzaib Rizvi',       '2005-01-11', 'House 7, Street 2, I-10/3, Islamabad',           '0333-9012345', 'Orthopaedic Ward B',      129, 1005, '2025-06-01', 15),
(3024, 'Mariam Iqbal',         '1960-08-02', 'House 1, Street 6, F-7/4, Islamabad',            null,           'Geriatric Ward',  132, 1006, '2025-06-05',  3),
(3025, 'Fahad Chaudhry',       '1987-10-31', 'House 13, Block 4, Gulberg Greens, Islamabad',   '0321-1234567', 'Cardiology Ward A',      133, 1007, '2025-06-10',  5),
(3026, 'Saitam Ali',           '1994-10-03', 'House 7, Street 9, G-10/2, Islamabad',           '0311-2345678', 'Orthopaedic Ward A',    104, 1008, '2025-06-15',  2),
(3027, 'Yasir Shah',           '2000-01-05', 'House 33, Block B, Faisal Town, Lahore',         '0345-5678901', 'Oncology Ward',  135, 1009, '2025-07-01',  7),
(3028, 'Zubair Spiegel',       '1982-06-26', 'House 11, Cantt Area, Rawalpindi',               '0300-7654321', 'Gastroenterology Ward',   138, 1010, '2025-07-05', 11),
(3029, 'Haris Yagami',         '1988-02-28', 'House 9, Street 5, F-10/2, Islamabad',           '0321-8901234', 'Pulmonology Ward', 139, 1011, '2025-07-10', 12),
(3030, 'Zeeshan Agha',         '2003-09-03', 'Flat 3, Butterfly Residency, E-7, Islamabad',    '0333-4123456', 'Cardiology Ward B',       128, 1012, '2025-07-15', 14)
go

-- Assigned_To
insert into Assigned_To (DateAdmitted, PatientNo, WardName) values
('2025-01-05',  3001, 'Orthopaedic Ward A'),
('2025-01-10',  3002, 'Orthopaedic Ward A'),
('2025-01-15',  3003, 'Geriatric Ward'),
('2025-01-20',  3004, 'Geriatric Ward'),
('2025-02-01',  3005, 'Cardiology Ward A'),
('2025-02-05',  3006, 'Cardiology Ward A'),
('2025-02-10',  3007, 'Neurology Ward'),
('2025-02-15',  3008, 'Neurology Ward'),
('2025-03-01',  3009, 'Oncology Ward'),
('2025-03-05',  3010, 'Oncology Ward'),
('2025-03-10',  3011, 'Oncology Ward'),
('2025-03-15',  3012, 'Paediatric Ward'),
('2025-03-20',  3013, 'Paediatric Ward'),
('2025-04-01',  3014, 'Dermatology Ward'),
('2025-04-05',  3015, 'Dermatology Ward'),
('2025-04-10',  3016, 'Psychiatric Ward'),
('2025-04-15',  3017, 'Gastroenterology Ward'),
('2025-04-20',  3018, 'Gastroenterology Ward'),
('2025-05-01',  3019, 'Pulmonology Ward'),
('2025-05-05',  3020, 'Pulmonology Ward'),
('2025-05-10',  3021, 'Cardiology Ward B'),
('2025-05-15',  3022, 'Cardiology Ward B'),
('2025-06-01',  3023, 'Orthopaedic Ward B'),
('2025-06-05',  3024, 'Geriatric Ward'),
('2025-06-10',  3025, 'Cardiology Ward A'),
('2025-06-15',  3026, 'Orthopaedic Ward A'),
('2025-07-01',  3027, 'Oncology Ward'),
('2025-07-05',  3028, 'Gastroenterology Ward'),
('2025-07-10',  3029, 'Pulmonology Ward'),
('2025-07-15',  3030, 'Cardiology Ward B')
go

-- complaints 
insert into complaint (PatientNo, Description) values
(3001, 'Fractured right shoulder following a fall from height'),
(3001, 'Elevated inflammatory markers indicating internal soft tissue damage'),
(3002, 'Acute lower back pain with radiculopathy extending to left leg'),
(3003, 'Severe ligament tear in left forearm following sports injury'),
(3004, 'Chronic hypertension with poorly controlled blood pressure readings'),
(3004, 'Degenerative joint condition presenting in both knees'),
(3005, 'Acute vision loss in left eye with suspected retinal involvement'),
(3006, 'Compression injury to thoracic vertebrae following heavy labour'),
(3007, 'Left orbital socket trauma with periorbital swelling and bruising'),
(3007, 'Peripheral nerve damage in right upper limb with sensory loss'),
(3008, 'Widespread skin abrasions with subcutaneous bruising on torso'),
(3009, 'Recurrent motion sickness with vomiting and inner ear dysfunction'),
(3010, 'Multiple rib fractures on right side with associated pleuritic pain'),
(3011, 'Chronic thoracolumbar back compression from occupational strain'),
(3012, 'Elevated intraocular pressure with early signs of optic nerve stress'),
(3013, 'Bilateral forearm muscle tears from repetitive overhead strain'),
(3014, 'Bilateral tibial stress fractures with periosteal tenderness'),
(3015, 'Frostbite injury to fingertips of both hands following cold exposure'),
(3016, 'Advanced age multi-organ deterioration with declining renal function'),
(3017, 'Rotator cuff tear in right shoulder from repetitive overhead activity'),
(3017, 'Acute gastritis with epigastric pain and confirmed H. pylori infection'),
(3018, 'Prosthetic limb attachment site inflammation with chronic nerve pain'),
(3019, 'Lower limb temporary paralysis following acute nerve compression'),
(3020, 'Cardiac arrhythmia presenting with palpitations and syncopal episodes'),
(3021, 'Recurrent viral myocarditis with breathlessness on mild exertion'),
(3022, 'Chronic fatigue syndrome with unexplained muscle weakness and lethargy'),
(3023, 'Left-sided frostbite injury to hand and forearm following cold exposure'),
(3024, 'Alcoholic liver disease with raised bilirubin and impaired liver enzymes'),
(3025, 'Internal organ compression secondary to blunt abdominal trauma'),
(3026, 'Psychosomatic chest pain with normal ECG and cardiac enzyme results'),
(3027, 'Generalised fatigue and weakness with electrolyte imbalance'),
(3028, 'Radiation-induced nausea and vomiting following occupational exposure'),
(3029, 'Severe sleep deprivation with associated anxiety and visual disturbance'),
(3030, 'Vocal cord strain with dysphonia following prolonged vocal overuse')
go

-- treatments

insert into Treatment (TreatmentDate, ComplaintID, DoctorStaffNo, TreatmentType, TreatmentEnd) values
('2025-01-06',  1, 1005, 'Open reduction and internal fixation surgery',          '2025-02-20'),
('2025-01-26',  2, 1006, 'Anti-inflammatory infusion and physiotherapy sessions', null),
('2025-01-11',  3, 1005, 'Conservative management with analgesia and bed rest',   null),
('2025-01-16',  4, 1007, 'Ligament repair physiotherapy and splinting',           '2025-02-16'),
('2025-01-21',  5, 1008, 'Antihypertensive medication and dietary counselling',   null),
('2025-01-21',  6, 1008, 'Orthopaedic review and joint replacement consultation', '2025-03-21'),
('2025-02-02',  7, 1005, 'Urgent ophthalmology referral and corticosteroid drops','2025-03-02'),
('2025-02-06',  8, 1006, 'Spinal decompression therapy and pain management',      '2025-02-20'),
('2025-02-11',  9, 1007, 'Reconstructive orbital surgery under general anaesthesia','2025-04-11'),
('2025-02-11', 10, 1007, 'Nerve conduction study and neurorehabilitation therapy', null),
('2025-02-16', 11, 1008, 'Wound debridement, dressing changes and skin grafting', '2025-03-20'),
('2025-03-02', 12, 1009, 'Vestibular rehabilitation and antiemetic medication',   null),
('2025-03-06', 13, 1010, 'Rib taping, intercostal nerve block and analgesia',     '2025-03-26'),
('2025-03-11', 14, 1011, 'Spinal decompression physiotherapy and TENS therapy',   null),
('2025-03-16', 15, 1012, 'Intraocular pressure-lowering eye drops and monitoring','2025-04-05'),
('2025-03-21', 16, 1013, 'Muscle rest protocol and targeted physiotherapy',       '2025-03-30'),
('2025-04-02', 17, 1005, 'Bone density scan, calcium supplementation and review', null),
('2025-04-06', 18, 1006, 'Rewarming therapy and frostbite wound management',      '2025-05-06'),
('2025-04-11', 19, 1007, 'Multi-organ supportive care and palliative management', null),
('2025-04-16', 20, 1008, 'Rotator cuff surgical repair and post-op rehabilitation','2025-06-16'),
('2025-04-16', 21, 1008, 'Proton pump inhibitor therapy and H. pylori eradication','2025-05-16'),
('2025-04-21', 22, 1009, 'Prosthetic site revision surgery and nerve reattachment', null),
('2025-05-02', 23, 1010, 'Nerve decompression physiotherapy and functional rehab', null),
('2025-05-06', 24, 1011, 'Cardiac rhythm restoration via pharmacological cardioversion','2025-05-20'),
('2025-05-11', 25, 1012, 'Antiviral therapy and cardiology follow-up protocol',   null),
('2025-04-02', 12, 1009, 'Cognitive vestibular therapy as secondary intervention','2025-05-02'),
('2025-02-02',  7, 1005, 'Emergency steroid injection for acute pressure relief',  '2025-02-10'),
('2025-03-16', 15, 1012, 'Corrective lens fitting and visual acuity monitoring',  '2025-04-10'),
('2025-01-06',  1, 1006, 'Post-operative pain management and wound care',         '2025-03-06'),
('2025-04-11', 19, 1007, 'Organ function monitoring with haemodialysis support',  '2025-06-11'),
('2025-05-20', 26, 1013, 'Fatigue management programme and nutritional support',  null),
('2025-06-03', 27, 1005, 'Cryotherapy and wound management for frostbite injury', '2025-07-03'),
('2025-06-08', 28, 1006, 'Hepatology review, liver support therapy and counselling',null),
('2025-06-13', 29, 1007, 'Abdominal CT, organ monitoring and pain management',    null),
('2025-06-18', 30, 1008, 'Psychiatric evaluation and cognitive behavioural therapy','2025-07-18'),
('2025-07-03', 31, 1009, 'IV fluid therapy and electrolyte correction protocol',  null),
('2025-07-08', 32, 1010, 'Chelation therapy and radiation safety monitoring',     '2025-08-08'),
('2025-07-13', 33, 1011, 'Sleep hygiene programme and anxiolytic medication',     null),
('2025-07-18', 34, 1012, 'Voice rest protocol and speech therapy sessions',       null)
go

-- belongs_to_team
insert into belongs_to_team (DoctorStaffNo, ConsultantStaffNo, DateJoinedTeam, DateLeaveTeam) values
(1005, 1001, '2023-01-01', null),
(1006, 1001, '2023-01-01', '2024-06-30'),
(1007, 1002, '2023-03-01', null),
(1008, 1002, '2023-03-01', null),
(1009, 1003, '2023-06-01', null),
(1010, 1003, '2023-06-01', '2024-12-31'),
(1011, 1004, '2024-01-01', null),
(1012, 1004, '2024-01-01', null),
(1013, 1001, '2024-07-01', null),
(1014, 1002, '2024-09-01', null),
(1006, 1003, '2025-01-01', null),
(1010, 1004, '2025-01-01', null)
go

-- grades
insert into grades (ConsultantStaffNo, DoctorStaffNo, ReviewDate, Grade) values
(1001, 1005, '2023-07-01', 'A'),
(1001, 1005, '2024-01-01', 'A'),
(1001, 1006, '2023-07-01', 'B'),
(1001, 1006, '2024-01-01', 'B'),
(1002, 1007, '2023-09-01', 'A'),
(1002, 1007, '2024-03-01', 'A'),
(1002, 1008, '2023-09-01', 'B'),
(1002, 1008, '2024-03-01', 'A'),
(1003, 1009, '2023-12-01', 'C'),
(1003, 1009, '2024-06-01', 'B'),
(1003, 1010, '2023-12-01', 'B'),
(1003, 1010, '2024-06-01', 'A'),
(1004, 1011, '2024-07-01', 'B'),
(1004, 1011, '2025-01-01', 'A'),
(1004, 1012, '2024-07-01', 'C'),
(1004, 1012, '2025-01-01', 'B'),
(1001, 1013, '2025-01-07', 'B'),
(1002, 1014, '2025-03-01', 'C'),
(1003, 1006, '2025-07-01', 'A'),
(1004, 1010, '2025-07-01', 'B')
go

-- previous experience
insert into previous_experience (DoctorStaffNo, FromDate, ToDate, Position, Establishment) values
(1005, '2018-01-01', '2020-12-31', 'Junior Houseman',     'Pakistan Institute of Medical Sciences, Islamabad'),
(1005, '2021-01-01', '2022-12-31', 'Senior Houseman',     'Shaukat Khanum Memorial Cancer Hospital, Lahore'),
(1006, '2017-06-01', '2019-05-31', 'Junior Houseman',     'Services Hospital, Lahore'),
(1006, '2019-06-01', '2021-06-30', 'Senior Houseman',     'Aga Khan University Hospital, Karachi'),
(1007, '2019-01-01', '2021-12-31', 'Junior Houseman',     'Jinnah Postgraduate Medical Centre, Karachi'),
(1007, '2022-01-01', '2022-12-31', 'Assistant Registrar', 'Fatima Memorial Hospital, Lahore'),
(1008, '2020-03-01', '2022-03-31', 'Junior Houseman',     'Holy Family Hospital, Rawalpindi'),
(1009, '2019-07-01', '2021-06-30', 'Junior Houseman',     'Lady Reading Hospital, Peshawar'),
(1009, '2021-07-01', '2023-06-30', 'Senior Houseman',     'Hayatabad Medical Complex, Peshawar'),
(1010, '2018-01-01', '2020-12-31', 'Junior Houseman',     'Civil Hospital, Karachi'),
(1010, '2021-01-01', '2022-12-31', 'Senior Houseman',     'Liaquat National Hospital, Karachi'),
(1011, '2020-06-01', '2022-05-31', 'Junior Houseman',     'Nishtar Hospital, Multan'),
(1012, '2021-01-01', '2023-12-31', 'Junior Houseman',     'Benazir Bhutto Hospital, Rawalpindi'),
(1013, '2022-01-01', '2023-12-31', 'Student',             'Quaid-e-Azam Medical College, Bahawalpur'),
(1014, '2024-01-01', null,         'Student',             'Ivor Paine Memorial Hospital')
go

print 'all data inserted successfully'
go

select *
from grades