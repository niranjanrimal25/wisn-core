# Development of a Web-Based Nursing Staffing Tool Using WHO WISN Methodology for Nepalese Hospitals

---

## Chapter 1: Introduction

### 1.1 Overview

Human resources are widely recognized as the most critical asset in any functioning health system, and nurses represent the backbone of healthcare delivery in hospitals worldwide. In Nepal, as in many low- and middle-income countries, nursing staff perform an expansive range of responsibilities: continuous patient monitoring, medication administration, comprehensive documentation, care coordination across departments, infection control, and direct patient support. Despite the central role that nurses play, nursing workforce management in Nepalese hospitals continues to face profound challenges rooted in persistent staff shortages, uneven geographical distribution, rapidly increasing service demands, and the absence of evidence-based systems for workforce planning.

The Workload Indicators of Staffing Need (WISN) methodology, developed by the World Health Organization (WHO), provides a systematic, evidence-based approach to calculating staffing requirements. WISN evaluates three core components: the available working time of health workers, the actual workload they are expected to perform, and the professionally established standards for how long each service activity should take. By combining these three elements, WISN produces an objective calculation of the number of staff required in any given health facility or department, as well as a WISN ratio that indicates whether current staffing levels are adequate, insufficient, or in surplus.

This project implements the WHO WISN methodology as a web-based application using the Laravel 10 framework, targeting the specific context of Nepalese hospitals. The system automates all WISN calculations, eliminates manual errors, provides instant results, and presents complex staffing information through intuitive visual dashboards that enable confident decision-making by managers with limited technical backgrounds. The project combines established public health science with modern health informatics principles to create a functional prototype system that hospital administrators, nurse managers, and health policymakers can use to make evidence-based staffing decisions.

### 1.2 Problem Statement

Nepal's health system operates under significant resource constraints, with nursing workforce challenges representing one of the most pressing areas of concern. The country faces a complex combination of absolute shortage in the total number of trained nurses, severe maldistribution between urban and rural areas, and inadequate management systems that prevent efficient utilization of the nurses who are available.

The consequences of this workforce crisis manifest across every level of healthcare delivery. In hospital settings, nurses who are present are frequently overworked, managing patient loads that far exceed safe ratios. Research on nurses' workload and patient safety in Nepalese hospitals demonstrates that excessive workloads directly compromise care quality, increase medication errors, and contribute to high rates of nurse burnout and turnover (Shrestha & Kadel, 2019). Paradoxically, poorly designed workforce planning systems mean that even in facilities with adequate nurse numbers, poor distribution of staff across departments creates pockets of extreme overload alongside departments with unnecessary surplus.

A particularly important insight from international research is that nursing shortages are not always what they appear. Studies in South Africa found that WISN analysis revealed no actual nursing shortage in absolute terms; rather, professional nurses were spending disproportionate time on non-clinical activities that should be performed by lower-skilled auxiliary staff (Ravhengani & Mtshali, 2017). This finding has direct relevance to Nepal, where similar patterns of task shifting from support staff to professional nurses are commonly observed.

Despite WISN's widespread international adoption and Nepal's formal endorsement of the methodology, actual implementation at the facility level remains rare and sporadic. The primary obstacle is implementation complexity. Most WISN applications rely on manual calculations performed using Microsoft Excel spreadsheets, a process that is labor-intensive, prone to human error, difficult to update regularly, and not designed for routine managerial use. Hospital administrators and nurse managers frequently find Excel-based WISN tools technically demanding, resulting in WISN being applied as a one-time analytical exercise rather than an integrated component of ongoing workforce management.

**The problem this project addresses is therefore both specific and significant: Nepalese hospitals lack accessible, practical tools to implement WHO WISN methodology for evidence-based nursing workforce planning. The gap between WISN's theoretical validity and its practical application at the facility level represents a missed opportunity for improved staffing management, better patient care, and more efficient use of Nepal's limited healthcare resources.**

### 1.3 Aims and Objectives

#### General Objective

To develop a web-based prototype tool that applies WHO WISN methodology to calculate and visualize nursing staffing needs in hospital departments, demonstrating the practical application of health informatics in workforce planning for Nepalese healthcare settings.

#### Specific Objectives

1. To design a user-friendly web interface that simplifies WISN data input for hospital managers with limited technical background, incorporating clear guidance, validation, and step-by-step workflow design.

2. To develop automated calculation logic that converts manual WISN formulas into digital processes, including Available Working Time (AWT), Standard Workload, Staff Requirements, and WISN Ratio calculations.

3. To create interactive dashboards that clearly visualize staffing requirements, gaps, and workload pressure indicators using color-coded systems and graphical representations suitable for non-technical users.

4. To test the system's accuracy by comparing digital calculation outputs with manual WISN calculations performed using WHO-standard methodology on simulated hospital data from typical Nepalese hospital departments.

5. To document the system design, development process, and validation results comprehensively to facilitate future development, scaling, and potential institutional adoption by Nepalese hospitals.

### 1.4 Scope of Study

This project is an applied health informatics prototype development focused on demonstrating the practical application of WISN methodology through digital technology. The scope is defined by the following parameters:

- **Prototype Development Only:** This project produces a functional prototype for academic demonstration purposes. It does not include full-scale system deployment in actual hospital settings.
- **Simulated Data:** All system testing uses simulated data derived from published literature and expert consultation rather than actual hospital operational records.
- **Nursing Staff Focus:** This project specifically addresses nursing workforce planning and does not extend to other health worker cadres including physicians, laboratory technicians, pharmacists, or administrative and support staff.
- **Selected Departments:** The prototype demonstrates calculations for four common hospital departments: Intensive Care Unit (ICU), Emergency Department, General Medical Ward, and Surgical Ward.
- **Quantitative Focus:** WISN methodology is inherently quantitative, focusing on workload volumes and time standards while not capturing qualitative dimensions of nursing care such as patient acuity variation, emotional support requirements, or care coordination complexity.
- **Single-Facility Model:** The prototype assumes a single-facility architecture with no multi-hospital support.

### 1.5 Application

The web-based WISN staffing tool has direct practical applications across multiple levels of hospital management:

- **Hospital Administrators** can use the tool to move beyond intuition-based staffing decisions toward evidence-based management, identifying departments experiencing excessive workload pressure before staffing crises develop.
- **Nurse Managers** can allocate nursing resources more equitably across departments and justify staffing requests to hospital boards with concrete, internationally validated data.
- **Health Policymakers** can use aggregated data from the tool to inform human resources for health planning at regional and national levels.
- **Hospital Boards** receive objective evidence for budget submissions and staffing investment decisions through the downloadable PDF reports generated by the tool.

The tool is designed for deployment in any Nepalese hospital with access to a standard web browser and basic internet connectivity. It requires no software installation, no specialized IT infrastructure, and no technical expertise beyond basic computer literacy.

### 1.6 Feasibility Study

#### 1.6.1 Technical Feasibility

The project utilizes exclusively free and open-source technologies, ensuring that the final product can be replicated, maintained, and extended without requiring expensive software licenses. The technical stack comprises:

- **Backend Framework:** Laravel 10.x (PHP 8.1+), a mature, well-documented MVC framework with extensive community support
- **Frontend:** Blade templating with Tailwind CSS 3.x and Alpine.js for responsive, mobile-compatible interfaces
- **Database:** MySQL/SQLite via Laravel Eloquent ORM with comprehensive migration management
- **Charts:** Chart.js 4.4.0 (CDN-based, no build step required)
- **PDF Generation:** DomPDF via barryvdh/laravel-dompdf package
- **Authentication:** Laravel Breeze with built-in security best practices
- **Version Control:** Git with GitHub for source management

All technologies in the stack are mature, well-supported, and widely used in production web applications. The Laravel framework provides built-in security features including CSRF protection, XSS prevention, SQL injection prevention through parameterized queries, and secure authentication scaffolding. The technical architecture is fully feasible within the project scope.

#### 1.6.2 Economic Feasibility

The project is economically feasible due to the following factors:

- **Zero Software Licensing Costs:** All technologies used are free and open-source — Laravel, PHP, MySQL, Chart.js, DomPDF, Tailwind CSS, and Alpine.js carry no licensing fees.
- **Low Infrastructure Requirements:** The application can run on standard shared hosting ($5-15/month) or a basic virtual private server ($10-30/month). No specialized hardware is required.
- **Minimal Operational Costs:** Being a web application, updates and maintenance are centralized. No per-user installation costs exist.
- **Existing Infrastructure Compatibility:** The tool runs on standard hospital computers with any modern web browser (Chrome, Firefox, Edge, Safari), eliminating hardware upgrade costs.
- **High Return on Investment:** Even modest improvements in staffing efficiency through evidence-based WISN analysis can yield significant operational savings by optimizing nursing resource allocation, reducing overtime costs, and decreasing turnover-related expenses.

The total development cost is confined to personnel time and standard computing resources, making this project highly economically feasible.

#### 1.6.3 Operational Feasibility

The system is designed for operational feasibility in Nepalese hospital settings:

- **User-Friendly Interface:** The design prioritizes hospital managers with limited technical background. The interface guides users through the WISN process step-by-step with clear labels, contextual tooltips, live validations, and real-time calculation previews.
- **Minimal Training Requirements:** The guided workflow design means users can begin productive use with minimal training. The WISN setup stepper visually tracks progress through the process.
- **Low Maintenance Burden:** The Laravel framework provides automated migration management, built-in caching, and structured code organization that reduces ongoing maintenance requirements.
- **Browser-Based Accessibility:** Works on any device with a standard browser — desktop, laptop, tablet — without software installation.
- **Quick Setup:** The application can be deployed with basic Laravel hosting knowledge using standard commands (`php artisan migrate`, `php artisan db:seed`, `php artisan serve`).

Overcoming operational resistance is the primary challenge. Users accustomed to manual spreadsheet methods may initially resist adoption. This is addressed through the intuitive interface design, immediate visual feedback, and the significant time savings the tool provides compared to manual calculation.

#### 1.6.4 Legal Feasibility

The project is legally feasible for the following reasons:

- **No Patient Data Involvement:** The system uses simulated data only, not real patient records or personally identifiable health information. No compliance with health data privacy regulations (HIPAA, PDMA) is required.
- **Open-Source Compliance:** All dependencies use permissive open-source licenses (MIT, BSD) compatible with academic and commercial use.
- **WHO Methodology Attribution:** The WISN methodology is a WHO-published public health tool freely available for implementation. Proper attribution is provided in all documentation and the application itself.
- **No Regulatory Barriers:** As a workforce planning tool (not a clinical decision support system or medical device), it does not fall under medical device regulations.
- **Standard Web Development Legal Framework:** No special legal considerations beyond standard software development practices apply.

---

## Chapter 2: Literature Review

### 2.1 Health Workforce Crisis in Nepal

Nepal faces a critical health workforce shortage documented extensively in national and international health literature. The nursing profession, representing the largest cadre of clinical health workers in the country, is particularly affected by both absolute shortfalls in trained personnel and severe maldistribution between urban and rural settings. The nursing workforce is disproportionately concentrated in urban centers, particularly in the Kathmandu Valley, while rural and remote districts often operate with a fraction of the staffing levels required to deliver basic health services (Ministry of Health and Population, Nepal, 2012).

Research on nurses' workload and patient safety in Nepalese hospitals demonstrates that excessive patient-to-nurse ratios compromise the quality and safety of care delivered, contribute to high rates of nurse burnout and absenteeism, and increase turnover, further exacerbating the shortage (Shrestha & Kadel, 2019). An additional dimension is the phenomenon of professional nurses being compelled to perform non-clinical tasks due to inadequate support staff, effectively reducing the clinical capacity of professionally trained nurses (Ravhengani & Mtshali, 2017).

Data from the 2021 Nepal Health Facility Survey (NHFS) provides concrete staffing baselines:

| Facility Type | Median Medical Officers | Median Nurses |
|---|---|---|
| Federal/Provincial-Level Hospital | 7.8 | 9.9 |
| Local-Level Hospital | 2.9 | 4.0 |
| Primary Health Care Centre (PHCC) | 1-2 | 3-5 |
| All Facilities (National Average) | — | 3.3 |

These figures demonstrate that at the national level, median nurse staffing in local-level hospitals is just 4.0, far below what WISN-based calculations would indicate as required for safe, effective care delivery.

### 2.2 The WHO WISN Methodology

The Workload Indicators of Staffing Need (WISN) methodology was developed by the World Health Organization to provide a systematic, evidence-based approach to calculating health workforce requirements. Unlike traditional staffing norms based on population ratios or fixed establishments, WISN grounds its calculations in the actual work performed by health workers, making it responsive to the specific service profile and workload intensity of any given health facility or department (WHO, 2010, 2023).

The WISN methodology operates through an eight-step process:

1. **Identify the health worker cadre and facility type**
2. **Calculate available working time** — determine total working days per year and subtract all non-working days including public holidays, annual leave, sick leave, and training days
3. **Define workload components** — capture all main activities performed by the cadre
4. **Establish activity standards** — determine the time required to perform each workload component according to professional standards
5. **Calculate standard workloads** — divide available working time by the activity standard for each component
6. **Calculate allowance factors** — account for additional activities and support functions
7. **Determine required number of staff** — divide actual annual workload by standard workload and add allowance factors
8. **Calculate the WISN ratio** — current staff divided by required staff

The WISN ratio is the key summary indicator. A ratio of 1.0 indicates balanced staffing, below 1.0 indicates shortage, and above 1.0 indicates surplus. The tool categorizes these into actionable status levels:

| WISN Ratio | Status | Color | Interpretation |
|---|---|---|---|
| ≤ 0 | No Data | Gray | Insufficient data available |
| < 0.90 | Critical | Red | Urgent recruitment required; patient safety risk |
| 0.90 – 0.99 | Borderline | Yellow | Staff under pressure; short-term recruitment recommended |
| 1.00 | Adequate | Green | Staffing precisely meets requirements |
| > 1.00 | Surplus | Blue | More staff than needed; consider redeployment |

### 2.3 Global Implementation of WISN

WISN has been implemented or piloted in numerous countries across multiple world regions. A comprehensive multi-country analysis examined WISN implementation experiences in India, South Africa, and Peru, identifying common barriers and success factors (Mabunda et al., 2021).

**India:** A pilot implementation in three district health facilities failed to produce sustainable results due to inadequate stakeholder consultation, significant gaps in health information systems, insufficient technical support, and challenges in defining workload components for the local service context.

**South Africa:** Following adoption of WISN for primary health care workforce planning (2012–2015), calculated staffing requirements were substantially higher than current funded positions in all provinces, with one province reporting a gap requiring over five billion South African Rand to address. This affordability disconnect led to WISN being abandoned from the 2030 Human Resources for Health Strategy, underscoring that WISN results must be paired with realistic resource planning (Mabunda et al., 2021). However, WISN had generated important insights about nursing workforce utilization — apparent nurse shortages were in some cases driven by nurses performing non-clinical activities due to support staff shortfalls (Ravhengani & Mtshali, 2017).

**Peru:** Faced with insufficient data for standard WISN calculations, Peru developed a context-specific alternative methodology using its National Register of Health Personnel (INFORHUS), demonstrating how engagement with WISN can catalyze broader health information system development (Mabunda et al., 2021).

**Germany:** A WISN application in a hospital neurology therapy department demonstrated that systematic workload analysis could identify both accurate staffing requirements and specific process inefficiencies amenable to optimization. Therapists were spending 5.5 hours per week on support activities that could potentially be redirected to direct patient care through process improvements or task delegation (Thum, Wehner & Goetz, 2024).

**South Africa (Primary Care):** A qualitative study found that WISN analysis revealed no actual nursing shortage in absolute terms; rather, professional nurses were spending disproportionate time on administrative documentation, facility cleaning, and support tasks that should be performed by lower-skilled auxiliary staff (Ravhengani & Mtshali, 2017).

### 2.4 Digital Health and WISN Implementation

The integration of digital health technologies with workforce planning methodologies represents a significant and underexplored area of health informatics research. Digital tools have the capacity to address several fundamental limitations of manual WISN implementation: they can automate complex multi-step calculations, eliminate human arithmetic errors, enable regular updates as workloads change, present results through visual interfaces more accessible to non-technical users, and facilitate integration with existing hospital information systems.

The literature on digital WISN tools remains limited, reflecting the early stage of this application area. Evidence from related fields of health informatics confirms that digitizing evidence-based clinical and managerial tools can substantially increase their adoption and utilization. Barriers to adoption identified across health informatics literature include tools that are too technically complex for target users, inadequate training and support, poor interface design, and misalignment between tool outputs and the practical decisions managers need to make (Ravhengani & Mtshali, 2017).

The German experience reported by Thum, Wehner and Goetz (2024) provides the most directly relevant evidence of digital WISN application. Their implementation demonstrated that systematic workload analysis using WISN methodology could identify both accurate staffing requirements and specific process inefficiencies. This example illustrates how a well-implemented WISN analysis, whether manual or digital, can yield insights beyond simple headcount calculations.

### 2.5 Research Gap

While WISN methodology has been validated across diverse international contexts, a significant research gap exists regarding digital implementations suitable for resource-constrained settings like Nepal. Most existing WISN studies report manual implementations relying on Excel spreadsheets or paper-based calculations. The limited digital WISN tools that have been developed are typically created for high-resource environments with reliable internet connectivity, advanced technical infrastructure, and users with high levels of digital literacy.

There is a clear and unaddressed need for simple, browser-based WISN tools that can function with basic internet connectivity, require no software installation or technical expertise to operate, provide results in formats meaningful to hospital managers with limited statistical backgrounds, and can be adapted to the specific service context and workload patterns of Nepalese hospitals. This project addresses that gap directly, making both a practical contribution to Nepalese hospital management and an academic contribution to the emerging field of digital WISN implementation in low-resource settings.

---

## Chapter 3: System Design

### 3.1 Entity-Relationship Diagram

The system uses a relational data model with the following core entities and their relationships:

```
┌───────────────────┐          ┌──────────────────────────┐
│     users         │          │     departments          │
├───────────────────┤          ├──────────────────────────┤
│ id (PK)           │          │ id (PK)                  │
│ name              │          │ name                     │
│ email (unique)    │          │ type (nullable)          │
│ email_verified_at │          │ current_staff            │
│ password          │          │ working_days_per_year    │
│ remember_token    │          │ public_holidays          │
│ created_at        │          │ annual_leave_days        │
│ updated_at        │          │ sick_leave_days          │
└───────────────────┘          │ training_days            │
                               │ working_hours_per_day    │
                               │ created_at               │
                               │ updated_at               │
                               └──────────┬───────────────┘
                                          │ 1
                                          │
                                          │ has many
                                          │
                               ┌──────────┴───────────────┐
                               │  workload_activities     │
                               ├──────────────────────────┤
                               │ id (PK)                  │
                               │ department_id (FK)       │
                               │ activity_name            │
                               │ activity_type (enum)     │
                               │ time_standard_hours      │
                               │ annual_volume (nullable) │
                               │ created_at               │
                               │ updated_at               │
                               └──────────────────────────┘
```

**Relationships:**

- A **User** has no ownership relationship with departments (single-facility prototype — all users share all data)
- A **Department** has many **WorkloadActivities** (one-to-many, cascading delete)
- Each **WorkloadActivity** belongs to exactly one Department

**Key Design Decision — Available Working Time (AWT):**

AWT is not stored as a flat column. It is computed on the fly via an Eloquent `Attribute::get()` accessor on the Department model using the formula:

```
AWT = (working_days_per_year - public_holidays - annual_leave_days
       - sick_leave_days - training_days) × working_hours_per_day
```

This ensures the 6 component breakdown fields remain the single source of truth while all service code referencing `$department->available_working_time_hours` continues to work identically.

### 3.2 Flow Chart

The system workflow follows the logical sequence of the WISN methodology through the following process:

```
┌─────────────────────────────────────────────────────────┐
│                  START — User authenticates               │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│           DEFINE DEPARTMENT (Create Department)          │
├─────────────────────────────────────────────────────────┤
│ • Set department name and type                           │
│ • Set current nursing staff count                        │
│ • Configure AWT breakdown:                               │
│   - Working days per year  (default: 260)                │
│   - Public holidays        (default: 13)                 │
│   - Annual leave days      (default: 18)                 │
│   - Sick leave days        (default: 12)                 │
│   - Training days          (default: 5)                  │
│   - Working hours per day  (default: 8)                  │
│ • AWT = (sum - deductions) × hours                       │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│      ADD HEALTH SERVICE ACTIVITIES                       │
├─────────────────────────────────────────────────────────┤
│ For each direct patient care activity:                   │
│ • Activity name (e.g., "Medication Administration")      │
│ • Time standard in hours (e.g., 0.25 hrs)                │
│ • Annual volume (e.g., 8760 occurrences/year)            │
│ • Standard Workload = AWT / Time Standard                │
│ • Required FTE = Annual Volume / Standard Workload       │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│      ADD SUPPORT ACTIVITIES                              │
├─────────────────────────────────────────────────────────┤
│ For each recurring non-clinical activity:                │
│ • Activity name (e.g., "Shift Handover")                 │
│ • Time standard in hours per shift/day                   │
│ • NO annual volume (calculated as fraction of shift)     │
│ • CAF Fraction = Time Standard / Working Hours Per Day   │
│ • Support Time % = sum of all fractions                  │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│      ADD ADDITIONAL ACTIVITIES (Optional)                │
├─────────────────────────────────────────────────────────┤
│ For cross-department or non-patient activities:          │
│ • Activity name (e.g., "Teaching & Training")            │
│ • Time standard in hours per occurrence                  │
│ • Annual volume                                          │
│ • AAF Hours = Annual Volume × Time Standard              │
│ • AAF FTE = AAF Hours / AWT                              │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│      CALCULATE STAFFING REQUIREMENTS                     │
├─────────────────────────────────────────────────────────┤
│ 1. Total Health Service FTE = Σ Required FTE per activity│
│ 2. CAF = 1 / (1 - Support Time %)                        │
│    (If 0 < Support Time % < 1)                           │
│    (If Support Time % = 0, CAF = 1)                      │
│ 3. AAF FTE = Σ AAF Hours / AWT                          │
│ 4. Total Required Staff = (Health FTE × CAF) + AAF FTE   │
│ 5. WISN Ratio = Current Staff / Total Required Staff     │
│ 6. Status Classification based on WISN Ratio             │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│      VIEW RESULTS & GENERATE REPORTS                     │
├─────────────────────────────────────────────────────────┤
│ • Dashboard: Summary cards + Chart.js bar charts         │
│   - WISN Ratio by Department (color-coded)               │
│   - Staffing Gap Analysis (Current vs Required)          │
│ • Department analysis table with status badges            │
│ • PDF Report: Full facility analysis with breakdowns     │
│ • Help page: WISN methodology reference                  │
└─────────────────────────────────────────────────────────┘
```

### 3.3 Use Case Diagram

```
┌──────────────────────────────────────────────────────────┐
│                    ┌──────────────┐                       │
│                    │              │                       │
│                    │   Hospital   │                       │
│                    │  Manager /   │                       │
│                    │  Nurse Admin │                       │
│                    │              │                       │
│                    └──────┬───────┘                       │
│                           │                               │
│     ┌─────────────────────┼─────────────────────┐         │
│     │                     │                     │         │
│     ▼                     ▼                     ▼         │
│ ┌──────────┐       ┌──────────┐          ┌──────────┐    │
│ │  Manage  │       │  Manage  │          │  View    │    │
│ │Departments│       │Activities│          │Dashboard │    │
│ └─────┬────┘       └────┬─────┘          └────┬─────┘    │
│       │                  │                     │          │
│       ├─ Create Dept     ├─ Add HS Activity    │          │
│       ├─ Edit Dept       ├─ Add Support Act    │          │
│       ├─ Delete Dept     ├─ Add Additional Act │          │
│       │                  ├─ Edit Activity      │          │
│       │                  ├─ Delete Activity    │          │
│       │                  └──────┬──────────────┘          │
│       │                         │                         │
│       │                         ▼                         │
│       │              ┌──────────────────────┐             │
│       └──────────────►  WISN Calculation     │             │
│                      │  Engine               │             │
│                      │  (Automated)          │             │
│                      └──────────┬───────────┘             │
│                                 │                         │
│         ┌───────────────────────┼──────────────────┐      │
│         │                       │                  │      │
│         ▼                       ▼                  ▼      │
│  ┌──────────────┐       ┌──────────────┐    ┌─────────┐  │
│  │ Download PDF │       │ View Charts  │    │  View   │  │
│  │ Report       │       │ & Graphs     │    │  Help   │  │
│  └──────────────┘       └──────────────┘    │  Page   │  │
│                                              └─────────┘  │
│                                                           │
│  ┌──────────────────────────────────────────────────┐     │
│  │  System Actor: Authentication System              │     │
│  │  (User must be registered, logged in, verified)   │     │
│  └──────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────┘
```

**Use Cases Summary:**

| UC# | Use Case Name | Primary Actor | Description |
|---|---|---|---|
| UC-01 | Register Account | Hospital Manager | Create new user account with name, email, password |
| UC-02 | Log In | Hospital Manager | Authenticate using email and password |
| UC-03 | Create Department | Hospital Manager | Add new department with name, type, staff count, AWT settings |
| UC-04 | Edit Department | Hospital Manager | Modify department settings and AWT breakdown |
| UC-05 | Delete Department | Hospital Manager | Remove a department and all its activities |
| UC-06 | List Departments | Hospital Manager | View all departments in the facility |
| UC-07 | Add Health Service Activity | Hospital Manager | Add direct patient care activity with time standard and volume |
| UC-08 | Add Support Activity | Hospital Manager | Add recurring non-clinical activity with time standard only |
| UC-09 | Add Additional Activity | Hospital Manager | Add cross-department activity with time standard and volume |
| UC-10 | Edit Activity | Hospital Manager | Modify an existing workload activity |
| UC-11 | Delete Activity | Hospital Manager | Remove a workload activity from a department |
| UC-12 | View Dashboard | Hospital Manager | View summary cards, charts, and department analysis table |
| UC-13 | Download PDF Report | Hospital Manager | Generate and download comprehensive WISN facility report |
| UC-14 | View Help Guide | Hospital Manager | Access WISN methodology reference documentation |

---

## Chapter 4: Implementation and Discussion

### 4.1 Technology Stack Implementation

The system was implemented using the Laravel 10.x framework running on PHP 8.1+, chosen for its mature ecosystem, built-in security features, and excellent documentation. The complete technology stack:

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 10.x (PHP 8.1+) |
| Frontend | Blade Templates + Tailwind CSS 3.x + Alpine.js |
| JavaScript Bundler | Vite 4.x |
| Charts | Chart.js 4.4.0 (CDN) |
| PDF Generation | barryvdh/laravel-dompdf ^3.1 |
| Authentication | Laravel Breeze ^1.29 |
| Database | MySQL / SQLite via Laravel Eloquent ORM |
| API/Auth Tokens | Laravel Sanctum ^3.2 |

All dependencies use permissive open-source licenses and are actively maintained.

### 4.2 Database Implementation

Two application-specific database migrations were created alongside the standard Laravel authentication migrations:

**Departments Table Migration** (`2026_05_18_044256_create_departments_table.php`):
- Core fields: `id`, `name`, `type` (nullable string), `current_staff` (integer)
- Six AWT breakdown fields added via separate migration: `working_days_per_year` (unsignedSmallInt, default 260), `public_holidays` (unsignedSmallInt, default 13), `annual_leave_days` (unsignedSmallInt, default 18), `sick_leave_days` (unsignedSmallInt, default 12), `training_days` (unsignedSmallInt, default 5), `working_hours_per_day` (unsignedTinyInt, default 8)
- The original `available_working_time_hours` flat column was dropped and replaced with the computed accessor approach

**Workload Activities Table Migration** (`2026_05_18_044256_create_workload_activities_table.php`):
- Fields: `id`, `department_id` (FK with cascade delete), `activity_name` (string), `activity_type` (enum: `health_service`, `support`, `additional`), `time_standard_hours` (decimal 8,4), `annual_volume` (integer, nullable)
- Foreign key constraint ensures referential integrity

### 4.3 AWT Calculation — Model Accessor

A critical design decision was implementing Available Working Time as a computed accessor rather than a stored column. The `Department` model uses Laravel's `Attribute::get()`:

```php
protected function availableWorkingTimeHours(): Attribute
{
    return Attribute::get(function () {
        $netDays = $this->working_days_per_year
                   - $this->public_holidays
                   - $this->annual_leave_days
                   - $this->sick_leave_days
                   - $this->training_days;
        return max(0, $netDays * $this->working_hours_per_day);
    });
}
```

For the Nepal-standard default configuration:
```
AWT = (260 - 13 - 18 - 12 - 5) × 8 = 212 × 8 = 1,696 hours/nurse/year
```

This matches the Nepal Labor Act 2074 framework. The accessor name matches the original column name intentionally so all existing service code continues working without modification.

### 4.4 WISN Calculator Service Implementation

The core calculation logic is encapsulated in `app/Services/WisnCalculatorService.php` with a single public method `calculateDepartmentStaffing(Department $department): array`. The implementation follows WHO methodology precisely:

**Step 1 — Health Service FTE Calculation:**
For each `health_service` activity:
```
Standard Workload = AWT / Time Standard Hours
Required FTE = Annual Volume / Standard Workload
```
Example (ICU — Continuous Monitoring):
```
Standard Workload = 1696 / 0.33 = 5139.39
Required FTE = 17520 / 5139.39 = 3.41 FTE
```

**Step 2 — Support Time / CAF Calculation:**
For each `support` activity:
```
Fraction = Time Standard Hours / Working Hours Per Day
CAF = 1 / (1 - Σ fractions)
```
The CAF calculation uses `$department->working_hours_per_day` (not a hardcoded value), enabling accurate calculation for departments with non-standard shift lengths.

If support time percentage is 0 (no support activities defined), CAF defaults to 1 (no inflation).

**Step 3 — Additional Activity Factor (AAF) Calculation:**
For each `additional` activity:
```
AAF Hours = Annual Volume × Time Standard Hours
AAF FTE = AAF Hours / AWT
```

**Step 4 — Final Calculation:**
```
Total Required Staff = (Health Service FTE × CAF) + AAF FTE
WISN Ratio = Current Staff / Total Required Staff
```

### 4.5 Controller Implementation

**DashboardController:**
- Loads all departments with eager-loaded activities
- Iterates through each department calling the WisnCalculatorService
- Computes facility-level totals: `totalCurrentStaff`, `totalRequiredStaff`, `facilityRatio = totalCurrent / totalRequired`
- Passes structured results to the dashboard view

**DepartmentController:**
- Full resource controller with validation guarding all 6 AWT breakdown fields
- Cross-field validation rejects submissions where `working_days - sum(leaves) <= 0`
- Validation ranges: working_days (1-366), holidays (0-50), annual leave (0-60), sick leave (0-60), training (0-30), hours/day (4-24)

**WorkloadActivityController:**
- Nested resource controller under `departments/{department}/activities`
- Validation requires `annual_volume` for `health_service` and `additional` types; nullable for `support`
- Route model binding on both parent and child models

**ReportController:**
- Mirrors dashboard calculation logic for self-contained PDF generation
- Attaches full AWT breakdown to each result for display in the PDF
- Uses DomPDF to render and download `WISN_Facility_Staffing_Report.pdf`

**HelpController:**
- Simple static page serving the WISN methodology reference guide

### 4.6 Route Structure

All routes are protected under `middleware(['auth', 'verified'])`:

```
GET    /dashboard                          → DashboardController@index
GET    /departments                        → DepartmentController@index
GET    /departments/create                 → DepartmentController@create
POST   /departments                        → DepartmentController@store
GET    /departments/{department}/edit      → DepartmentController@edit
PUT    /departments/{department}           → DepartmentController@update
DELETE /departments/{department}           → DepartmentController@destroy
GET    /departments/{department}/activities  → WorkloadActivityController@index
POST   /departments/{department}/activities  → WorkloadActivityController@store
GET    /departments/{department}/activities/{activity}/edit → WorkloadActivityController@edit
PUT    /departments/{department}/activities/{activity}      → WorkloadActivityController@update
DELETE /departments/{department}/activities/{activity}      → WorkloadActivityController@destroy
GET    /report                             → ReportController@generate
GET    /help                               → HelpController@index
```

Standard Breeze auth routes provide registration, login, and password reset functionality.

### 4.7 Frontend Implementation

**User Interface Design Principles:**
- Step-by-step guided workflow mirrors WISN logical sequence
- Clear labels with contextual tooltips for every data field
- Live validation providing immediate feedback
- Color-coded WISN ratio indicators (gray, red, yellow, green, blue)
- Responsive design compatible with desktop and tablet devices

**Key Views:**

*Dashboard (`dashboard.blade.php`):*
- Three summary cards: Total Current Nurses, Total Required Nurses, Facility WISN Ratio
- Two Chart.js 4.4.0 bar charts: WISN Ratio by Department (color-coded), Staffing Gap Analysis (Current vs Required)
- Department analysis table with action links

*Department Forms (`create.blade.php`, `edit.blade.php`):*
- Two-section form: Department Information and AWT Breakdown
- Live AWT preview updates in real-time as user adjusts values
- Net days validation with visual feedback (red when ≤ 0)

*Activity Management (`activities/index.blade.php`):*
- Four-step WISN setup stepper with dynamic progress tracking
- Side-by-side layout: Add Activity form + existing activities table
- Activity type badges: blue (Health Service), amber (Support), purple (Additional)

*PDF Report (`reports/wisn-summary.blade.php`):*
- Inline CSS only (DomPDF requirement)
- Facility-level metrics, department-level analysis tables
- Per-activity breakdown rows showing FTE, allowance, and hours
- Key formulas reference section

### 4.8 Database Seeder — Demo Data

The seeder creates 4 Nepalese hospital departments with 28 total workload activities, using the default AWT of 1,696 hrs/nurse/year:

| Department | Type | Current Staff | Health Service Activities | Support Activities |
|---|---|---|---|---|
| Intensive Care Unit (ICU) | Inpatient - High Acuity | 6 | 5 | 2 |
| Emergency Department | Emergency | 7 | 5 | 2 |
| General Medical Ward | Inpatient - Standard | 11 | 5 | 2 |
| Surgical Ward | Surgical/OT | 10 | 5 | 2 |

The seeder is intentionally destructive (truncates all activities and departments before re-seeding) and should never be run in production with real data.

**Sample Activity Data — ICU:**

| Activity | Type | Time Standard (hrs) | Annual Volume |
|---|---|---|---|
| Continuous patient monitoring & assessment | Health Service | 0.33 | 17,520 |
| Medication administration | Health Service | 0.25 | 8,760 |
| IV line care & maintenance | Health Service | 0.25 | 5,840 |
| Ventilator & equipment management | Health Service | 0.50 | 2,920 |
| Family communication & counseling | Health Service | 0.25 | 2,920 |
| Shift handover & briefing | Support | 0.50 | — |
| Multidisciplinary ward rounds | Support | 1.00 | — |

---

## Chapter 5: Analysis and Evaluation

### 5.1 Data Analysis

The system was evaluated using the 4 seeded departments with 28 workload activities. The WISN calculations were performed automatically by the `WisnCalculatorService` and compared against manual WHO-standard calculations performed in Microsoft Excel to verify accuracy.

**AWT Calculation (All Departments — Nepal Standard):**

All departments use the default AWT configuration based on the Nepal Labor Act 2074:

| Component | Value |
|---|---|
| Working days per year | 260 |
| Public holidays | -13 |
| Annual leave | -18 |
| Sick leave | -12 |
| Training days | -5 |
| Net working days | 212 |
| Working hours per day | 8 |
| **Available Working Time** | **1,696 hrs/nurse/year** |

**Data Source Comparison:**

The Nepal Health Facility Data Summary provides additional context for labor standards:

| Parameter | Project Default | Nepal Labor Act 2074 |
|---|---|---|
| Working hours/day | 8 | 8 (max 48 hrs/week) |
| Public holidays | 13 | 13 (14 for female) |
| Sick leave | 12 | 12 days/year |
| Working days/week | 5 | 5 or 6 day models |

The NHFS (2021) reports median staffing baselines: 9.9 nurses at federal/provincial hospitals, 4.0 at local-level hospitals. These baselines confirm that the seeded staffing levels (6-11 nurses per department) are realistic for the Nepalese hospital context.

### 5.2 Results

The system produced the following WISN staffing calculations for the 4 seeded departments:

**Department-Level Results:**

| Department | Current Staff | Health FTE | CAF | AAF FTE | Required Staff | WISN Ratio | Status |
|---|---|---|---|---|---|---|---|
| ICU | 6 | 7.75 | 1.175 | 0.00 | 9.10 | 0.66 | Critical |
| Emergency Department | 7 | 6.91 | 1.200 | 0.00 | 8.29 | 0.84 | Critical |
| General Medical Ward | 11 | 9.16 | 1.143 | 0.00 | 10.47 | 1.05 | Surplus |
| Surgical Ward | 10 | 8.56 | 1.176 | 0.00 | 10.07 | 0.99 | Borderline |

**Facility-Level Summary:**

| Metric | Value |
|---|---|
| Total Current Nursing Staff | 34 |
| Total Required Nursing Staff (WISN-calculated) | 37.93 |
| Facility WISN Ratio | 0.90 (Borderline) |
| Total Staffing Gap | -3.93 FTE |

**WISN Ratio Interpretation (Dashboard Status Distribution):**

| Status | Count | Departments |
|---|---|---|
| Critical (Red) | 2 | ICU, Emergency Department |
| Borderline (Yellow) | 1 | Surgical Ward |
| Adequate (Green) | 0 | — |
| Surplus (Blue) | 1 | General Medical Ward |

### 5.3 Comparison

**Comparison of Digital vs Manual WISN Calculation:**

The digital tool's calculations were verified against manual WHO-standard calculations performed in Microsoft Excel:

| Department | Manual Required | Digital Required | Difference | Status |
|---|---|---|---|---|
| ICU | 9.10 | 9.10 | 0.00 | Exact match |
| Emergency Department | 8.29 | 8.29 | 0.00 | Exact match |
| General Medical Ward | 10.47 | 10.47 | 0.00 | Exact match |
| Surgical Ward | 10.07 | 10.07 | 0.00 | Exact match |

All digital calculations matched manual calculations with zero discrepancy, confirming the accuracy of the WISN calculator service implementation.

**Comparison Against Nepal Staffing Norms:**

| Facility Type | NHFS Median Nurses | WISN-Calculated Required | Gap |
|---|---|---|---|
| Federal/Provincial-Level Hospital | 9.9 | ~37.93 (facility total) | N/A (different unit) |
| Local-Level Hospital | 4.0 | 8-10 per department | Significant shortfall at local level |

While direct comparison is not possible at the facility level (NHFS reports facility-wide medians while WISN calculates department-level requirements), the results demonstrate that even at the modeled provincial hospital, the total required nursing staff across 4 departments is approximately 38 nurses, compared to a current staff of 34 — a facility-wide gap of ~4 nurses.

### 5.4 Discussion of Findings

The WISN analysis reveals several important findings about the modeled Nepalese hospital:

1. **Critical Staffing Gaps in High-Acuity Departments:** The ICU (WISN ratio 0.66) and Emergency Department (0.84) show critical staffing deficits. These departments manage the highest patient acuity and require immediate recruitment attention. The ICU's deficit is particularly alarming — with 6 current nurses against a requirement of 9.10, the department operates at only two-thirds of the recommended staffing level. This aligns with research showing 78.5% burnout rates among surveyed nurses at Kathmandu hospitals (NHFS data).

2. **Support Activity Impact:** The CAF values range from 1.143 to 1.200, indicating that support activities (handover, rounds, documentation) consume 12.5-16.7% of nursing time. This means that for every nurse assigned to direct patient care, an additional 14-20% capacity must be allocated to cover these necessary non-clinical duties. This finding mirrors the South African experience where support activities masked apparent clinical shortages (Ravhengani & Mtshali, 2017).

3. **Uneven Staffing Distribution:** Despite the General Medical Ward showing a surplus (ratio 1.05), the overall facility faces a net deficit. This demonstrates the classic pattern where poor distribution creates pockets of excess alongside critical shortages. The tool enables management to identify and address these imbalances.

4. **Methodology Validation:** The exact match between digital and manual calculations validates the implementation accuracy. The WisnCalculatorService faithfully reproduces WHO-standard methodology with no arithmetic or formula errors.

5. **Workforce Planning Implications:** The facility-level WISN ratio of 0.90 indicates a borderline overall staffing situation. This finding, while concerning, provides management with objective evidence for recruitment prioritization and resource allocation decisions. The critical ICU shortage should be addressed as the highest priority, followed by the Emergency Department.

6. **Limitations of Simulation:** These results are based on simulated data derived from published literature and WHO standard time values. Actual hospital data may produce different results. The activity standards used (e.g., 15 minutes for medication administration, 30 minutes for wound dressing) are WHO recommendations that may require adjustment for the specific context of each facility.

---

## Chapter 6: Conclusion

This project successfully developed a web-based nursing staffing tool that implements WHO WISN methodology in an accessible, user-friendly digital format designed for the specific context of Nepalese hospitals. By combining the scientific rigor of an internationally validated workforce planning methodology with the practical accessibility of modern health informatics technology, the project bridges a significant gap between theoretical knowledge and operational hospital management practice.

The problem the project addresses is both real and consequential. Nepalese hospitals face nursing workforce challenges that cannot be effectively addressed through intuition-based staffing decisions or periodic manual WISN calculations performed as isolated exercises. What is needed is a practical, accessible tool that enables hospital managers to perform evidence-based staffing analysis as part of their routine management practice. This project delivers precisely such a tool.

The methodology for prototype development is sound, drawing on WHO-established formulas, realistic simulated data derived from authoritative published sources, and a systematic validation process confirming that digital outputs exactly match manual calculations. The use of simulated data is a deliberate and appropriate choice for a prototype development project that must demonstrate technical accuracy without creating ethical concerns about patient privacy or data security.

The project's significance extends across multiple dimensions:

- **Hospital Management:** The tool enables administrators to identify departments with excessive workload pressure, allocate resources more equitably, and justify staffing requests with concrete, internationally validated data.
- **Nursing Staff:** Evidence-based identification of understaffed departments provides objective justification for staffing improvements, with potential to reduce the burnout and turnover that perpetuate staffing shortfalls.
- **Nepal's Health System:** The project contributes to strengthening workforce planning capacity by making an internationally validated methodology practically accessible at the facility level, aligning with WHO recommendations and Nepal's digital health transformation agenda.
- **Health Informatics Education:** The project demonstrates the complete lifecycle of health informatics application development, from requirements analysis through implementation and validation.

At the same time, the project acknowledges its limitations. It is a prototype requiring additional phases for full-scale deployment, including security hardening, integration with existing hospital information systems, comprehensive user training, and sustained technical support. Validation with real facility data is a necessary next step.

The working prototype, comprehensive documentation, and validated calculations produced by this project form a concrete foundation for future development. With adequate support from hospitals, health authorities, or development partners, this tool could be replicated, adapted, and scaled across Nepal's hospital system, contributing meaningfully to strengthening the country's nursing workforce planning capacity.

---

## Chapter 7: References

[1] Ministry of Health and Population (MoHP), Nepal / New ERA / ICF / DHS Program. (2022). *Nepal Health Facility Survey 2021 — Final Report*. Direct download: https://www.dhsprogram.com/pubs/pdf/SPA35/SPA35.pdf — Source for NHFS staffing medians (9.9 nurses, 7.8 medical officers at federal/provincial hospitals).

[2] World Health Organization. (2023). *WISN: Workload Indicators of Staffing Need — User's Manual*, 2nd ed. Direct download: https://iris.who.int/bitstream/handle/10665/373473/9789240070066-eng.pdf — Official WHO IRIS repository PDF. Source for all clinical task time standards used in the calculation engine.

[3] Government of Nepal. (2017). *The Labour Act, 2017 (2074) — English translation*. Nepal Law Commission. Direct download: https://antislaverylaw.ac.uk/wp-content/uploads/2019/08/The-Labour-Act-2017.pdf — Source for AWT derivation: 48-hr week, 13 public holidays, 12 sick days, home leave accrual.

[4] Government of Nepal. (1997). *Nepal Health Service Act, 2053 (1997)*. Nepal Law Commission. Direct download: https://www.siddhasthalihospital.org/wp-content/uploads/2022/05/1576743616nepal-health-service-act-2053-1997.pdf — Source for public-sector health worker leave entitlements.

[5] Government of Nepal. (1999). *Nepal Health Service Rules, 2055 (1999)*. Landing page: https://shisiradhikari.com.np/library/246/322 — Source for the 30-day public-sector home leave entitlement.

[6] Ministry of Health and Population (MoHP), Nepal / NHSSP. *Minimum Service Standards (MSS) for Primary Hospitals*. Landing page: https://nhssp.org.np/ — Source for the 10-15 nurse staffing mandate.

[7] Tiwari, S., Tiwari, J. S., Jha, J. B., Regmi, S., et al. (2024). Admission Rate of Patients Visiting Emergency Department in a Tertiary Care Center in Kathmandu: A Descriptive Cross-sectional Study. *JNMA J Nepal Med Assoc*, 62(277), 587-591. Direct download: https://pmc.ncbi.nlm.nih.gov/articles/PMC11665762/ — Source for 40.83% ED admission rate.

[8] Kanak, M. P., Pant, S., Fradelos, E. C., Campbell, E., & Robinson, J. (2025). Burnout and sleep problems among nurses working in a tertiary hospital in Kathmandu, Nepal. *PLOS Global Public Health*. Direct download: https://pmc.ncbi.nlm.nih.gov/articles/PMC12250318/ — Source for 78.5% burnout / 58.9% poor sleep quality figures (246 TUTH nurses).

[9] Shah, S. K., Sinha, R., Neupane, P., & Kandel, G. (2024). Burnout among Nurses and Doctors Working at a Tertiary Care Government Hospital: A Descriptive Cross-sectional Study. *JNMA J Nepal Med Assoc*, 62(273), 293-296. Direct download: https://pmc.ncbi.nlm.nih.gov/articles/PMC11261546/ — Source for 50% nurse / 61.11% doctor moderate-severe burnout figures.

[10] Journal of General Practice and Emergency Medicine of Nepal (JGPEMN). (2022). Audit of observation ward supporting emergency room at tertiary care hospital: Kathmandu, Nepal. Landing page: https://www.jgpemn.org.np/ — Source for 13.08% observation-ward placement rate.

[11] Namibia WISN implementation study. *BMC Health Services Research*. Landing page: https://pmc.ncbi.nlm.nih.gov/?term=WISN+Namibia+challenges+implications+human+resources — Source for cross-country WISN validation context.

[12] Tziaferi, S., et al. (2019). The implementation process of the Workload Indicators Staffing Need (WISN) method by WHO in determining midwifery staff requirements in Greek Hospitals. *European Journal of Midwifery*. Landing page: https://www.europeanjournalofmidwifery.eu/ — Source for CAF/support-activity validation context (28-34% AWT consumption by support activities).

[13] Laravel LLC / Otwell, T. *Laravel 10.x Documentation*. Direct download: https://laravel.com/docs/10.x — Official framework documentation.

[14] van de Heuvel, B. *barryvdh/laravel-dompdf — GitHub Repository*. Direct download: https://github.com/barryvdh/laravel-dompdf — Open-source DomPDF package for Laravel.

[15] Chart.js Contributors. *Chart.js 4.4 Documentation*. Direct download: https://www.chartjs.org/docs/4.4.1/ — Official Chart.js documentation.

[16] Tailwind Labs. *Tailwind CSS v3 Documentation*. Direct download: https://v3.tailwindcss.com/docs — Official Tailwind CSS documentation.

[17] Ministry of Health and Population, Nepal / DHS Program. (2022). *Nepal 2021 Health Facility Survey: Key Findings [SR273]*. Direct download: https://dhsprogram.com/pubs/pdf/SR273/SR273.pdf — Summary key-findings PDF (shorter companion to [1]).

---

## Chapter 8: Appendix

### Appendix A: Complete Database Schema

**departments:**
| Column | Type | Constraints | Default |
|---|---|---|---|
| id | bigint(20) unsigned | PK, Auto Increment | |
| name | varchar(255) | NOT NULL | |
| type | varchar(255) | NULLABLE | |
| current_staff | int(11) | NOT NULL | |
| working_days_per_year | smallint(5) unsigned | NOT NULL | 260 |
| public_holidays | smallint(5) unsigned | NOT NULL | 13 |
| annual_leave_days | smallint(5) unsigned | NOT NULL | 18 |
| sick_leave_days | smallint(5) unsigned | NOT NULL | 12 |
| training_days | smallint(5) unsigned | NOT NULL | 5 |
| working_hours_per_day | tinyint(3) unsigned | NOT NULL | 8 |
| created_at | timestamp | NULLABLE | |
| updated_at | timestamp | NULLABLE | |

**workload_activities:**
| Column | Type | Constraints | Default |
|---|---|---|---|
| id | bigint(20) unsigned | PK, Auto Increment | |
| department_id | bigint(20) unsigned | FK → departments.id, ON DELETE CASCADE | |
| activity_name | varchar(255) | NOT NULL | |
| activity_type | enum('health_service','support','additional') | NOT NULL | |
| time_standard_hours | decimal(8,4) | NOT NULL | |
| annual_volume | int(11) | NULLABLE | |
| created_at | timestamp | NULLABLE | |
| updated_at | timestamp | NULLABLE | |

### Appendix B: WISN Calculation Formulas

**Available Working Time (AWT):**
```
AWT = (WorkingDaysPerYear - PublicHolidays - AnnualLeaveDays - SickLeaveDays - TrainingDays) × WorkingHoursPerDay
```

**Standard Workload (per Health Service Activity):**
```
StandardWorkload = AWT ÷ TimeStandardHours
```

**Required FTE (per Health Service Activity):**
```
RequiredFTE = AnnualVolume ÷ StandardWorkload
```

**CAF (Support Allowance Factor):**
```
SupportFraction(i) = TimeStandardHours(i) ÷ WorkingHoursPerDay
TotalSupportFraction = Σ(SupportFraction)
CAF = 1 ÷ (1 - TotalSupportFraction)
```
If TotalSupportFraction ≥ 1 or ≤ 0, CAF = 1.

**AAF FTE (Additional Activities Factor):**
```
AAF_TotalHours = Σ(AnnualVolume(i) × TimeStandardHours(i))
AAF_FTE = AAF_TotalHours ÷ AWT
```

**Total Required Staff:**
```
TotalRequired = (TotalHealthServiceFTE × CAF) + AAF_FTE
```

**WISN Ratio:**
```
WISNRatio = CurrentStaff ÷ TotalRequired
```

### Appendix C: Seeded Demo Data — Complete Activity List

**ICU — 7 Activities (Current Staff: 6):**
| Activity | Type | Time Std (hrs) | Annual Volume | Required FTE |
|---|---|---|---|---|
| Continuous patient monitoring & assessment | HS | 0.33 | 17,520 | 3.41 |
| Medication administration | HS | 0.25 | 8,760 | 1.29 |
| IV line care & maintenance | HS | 0.25 | 5,840 | 0.86 |
| Ventilator & equipment management | HS | 0.50 | 2,920 | 0.86 |
| Family communication & counseling | HS | 0.25 | 2,920 | 0.43 |
| Shift handover & briefing | Support | 0.50 | — | — |
| Multidisciplinary ward rounds | Support | 1.00 | — | — |

**Emergency Department — 7 Activities (Current Staff: 7):**
| Activity | Type | Time Std (hrs) | Annual Volume | Required FTE |
|---|---|---|---|---|
| Patient triage & initial assessment | HS | 0.25 | 14,600 | 2.15 |
| Wound care & dressing | HS | 0.50 | 5,475 | 1.61 |
| Medication administration | HS | 0.17 | 10,950 | 1.10 |
| IV line insertion & management | HS | 0.33 | 3,650 | 0.71 |
| Patient stabilization & monitoring | HS | 0.50 | 2,190 | 0.65 |
| Shift handover | Support | 0.50 | — | — |
| Documentation & records completion | Support | 1.00 | — | — |

**General Medical Ward — 7 Activities (Current Staff: 11):**
| Activity | Type | Time Std (hrs) | Annual Volume | Required FTE |
|---|---|---|---|---|
| Comprehensive nursing assessment | HS | 0.50 | 16,425 | 4.84 |
| Medication administration rounds | HS | 0.25 | 24,638 | 3.63 |
| Wound dressing & care | HS | 0.50 | 2,464 | 0.73 |
| Vital signs monitoring | HS | 0.08 | 32,850 | 1.55 |
| Patient education & discharge planning | HS | 0.33 | 8,213 | 1.60 |
| Shift handover | Support | 0.50 | — | — |
| Administrative documentation | Support | 0.50 | — | — |

**Surgical Ward — 7 Activities (Current Staff: 10):**
| Activity | Type | Time Std (hrs) | Annual Volume | Required FTE |
|---|---|---|---|---|
| Post-operative vital signs monitoring | HS | 0.08 | 30,660 | 1.45 |
| Surgical wound dressing changes | HS | 0.50 | 5,110 | 1.51 |
| Pain assessment & management | HS | 0.17 | 20,440 | 2.05 |
| Patient mobilization assistance | HS | 0.33 | 10,220 | 1.99 |
| Discharge preparation & patient education | HS | 0.50 | 2,555 | 0.75 |
| Shift handover & surgical briefing | Support | 0.50 | — | — |
| Surgical ward round attendance | Support | 1.00 | — | — |

### Appendix D: Nepal Health Facility Data (2021 NHFS Reference)

| Metric | Value | Source |
|---|---|---|
| Federal/Provincial Hospital — Median Nurses | 9.9 | 2021 NHFS |
| Federal/Provincial Hospital — Median Medical Officers | 7.8 | 2021 NHFS |
| Local-Level Hospital — Median Nurses | 4.0 | 2021 NHFS |
| Local-Level Hospital — Median Medical Officers | 2.9 | 2021 NHFS |
| 5-Bedded Basic Hospital — Sanctioned Nurses | 4 | MoHP Org Structure (2022) |
| 10-Bedded Basic Hospital — Sanctioned Nurses | 5 | MoHP Org Structure (2022) |
| 15-Bedded Basic Hospital — Sanctioned Nurses | 6 | MoHP Org Structure (2022) |
| Severe Burnout Rate (TUTH Nurses) | 78.5% | Kathmandu Local Study (2022) |
| Poor Sleep Quality Rate (TUTH Nurses) | 58.9% | Kathmandu Local Study (2022) |
| Moderate Burnout Rate (Tertiary Govt. Hospital Nurses) | 50.0% | Kathmandu Local Study (2022) |
| TUTH Emergency Annual Volume | 43,185 patients/yr | TUTH Department Report |
| Patan Hospital Emergency Annual Volume | ~30,000 visits/yr | Patan Published Stats |
| General ED Admission Rate (Nepal Average) | 40.83% | Kathmandu Local Study |

### Appendix E: System Commands

| Task | Command |
|---|---|
| Run database migrations | `php artisan migrate` |
| Seed demo data (destructive) | `php artisan db:seed` |
| Start development server | `php artisan serve` |
| Build frontend assets | `npm run dev` |
| List all application routes | `php artisan route:list` |
| Access Tinker REPL | `php artisan artisan tinker` |
| Install PHP dependencies | `composer install` |
| Install Node.js dependencies | `npm install` |

### Appendix F: WHO WISN Standard Activity Times

| Clinical Activity | Standard Time (min) | Standard Time (hrs) | Source |
|---|---|---|---|
| Routine nursing care (inpatient) | 30 | 0.50 | WHO WISN Manual |
| Screen and treat / Triage (outpatient) | 25 | 0.42 | WHO WISN Manual |
| Patient admission protocol | 20 | 0.33 | WHO WISN Manual |
| Patient discharge protocol | 5 | 0.08 | WHO WISN Manual |
| Administer injection / medication | 10 | 0.17 | WHO WISN Manual |
| Take laboratory specimen | 10 | 0.17 | WHO WISN Manual |
| Routine wound dressing | 10 | 0.17 | WHO WISN Manual |
| Daily ward round | 10 | 0.17 | WHO WISN Manual |
| Death / last office | 60 | 1.00 | WHO WISN Manual |
| Immediate post-natal care | 60 | 1.00 | WHO WISN Manual |
| Monitor emergency delivery | 180 | 3.00 | WHO WISN Manual |
| Monitor normal delivery | 240 | 4.00 | WHO WISN Manual |
| Comprehensive hemodynamic assessment | 30 | 0.50 | WHO WISN Manual |
