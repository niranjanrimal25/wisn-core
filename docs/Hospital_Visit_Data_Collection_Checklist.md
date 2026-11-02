# Hospital Visit — Data Collection Checklist

**Print this before the visit.** This form is for a Nepal hospital project exploring a digital WISN and inpatient Operations tool. Use it to ask about data availability, definitions, sources, and permission. Leave the spaces blank and write responses by hand.

> **Ethics and privacy:** Before copying records, conducting research interviews/surveys, or collecting identifiable information, confirm university ethics requirements and obtain required hospital approval. If approval is not yet in place, use tomorrow's visit only to discuss feasibility and data availability. Do not copy patient records, patient names, hospital identifiers, staff names, or photographs of records. Request aggregate, de-identified totals. Do not enter patient-identifying details in the app.

---

## 1. Visit details

Hospital/facility: __________________________________________________________

District / Province: ___________________________  Date: ______________________

Student/team members: ______________________________________________________

Hospital contact and role: __________________________________________________

Visit purpose: [ ] Feasibility discussion  [ ] Data request  [ ] Workflow observation  [ ] Other: __________

University ethics approval/reference (if issued): ______________________________

Hospital permission/contact/reference: _______________________________________

Permission status: [ ] Approved for this activity  [ ] Pending  [ ] Not yet requested

**If approval is pending:** do not collect/copy research data or administer a formal survey. Ask only whether the data exist, who owns them, and what approvals are required.

---

## 2. Recommended data period — decide this before requesting extracts

### Annual WISN staffing analysis (primary recommendation)

Request **one most recent, complete, consistent 12-month period** for a baseline WISN calculation. Use the hospital's established reporting/fiscal year if its records align with it. Write the exact start and end dates below. Do not mix calendar-year totals with fiscal-year staffing data.

Period start: __________________________  Period end: _________________________

Reporting year/name used by hospital: ________________________________________

For annual workload, request the **total count per activity for the full 12 months**. You do not need 365 daily records to calculate an annual WISN baseline. If available, ask for the same information by month (12 monthly totals); this helps identify missing months and seasonal patterns before summing to the annual total.

- [ ] One complete 12-month activity total is available.
- [ ] Monthly totals are available for all 12 months.
- [ ] Some months are missing; list them: ______________________________________
- [ ] Definitions or recording systems changed during the period; describe: _______
  ___________________________________________________________________________

**If the thesis will study trends:** ask whether the same annual totals and definitions are available for **3–5 years**. Five or more consistent years are preferable for trends/forecasting, but a single complete year can support a clearly labelled cross-sectional baseline. Two years can show a simple comparison but do not, by themselves, make a reliable forecast.

**Do not substitute a six-month period for an annual baseline without a justified method.** If only six months exist, request month-level totals, explain the limitation, and do not simply double the count unless seasonality and the annualisation assumption are defensible.

### Operational census and shift-staffing data (separate from annual WISN)

The Operations workflow records a directly observed patient census at **12:00 AM, 7:00 AM, and 7:00 PM**; actual nurses on duty are recorded at **7:00 AM and 7:00 PM only**. A short **2–4 week feasibility/pilot period** can test whether the hospital can record complete handovers, including weekdays and weekends. It is not enough to claim year-round or seasonal staffing patterns. For year-round variation, use a longer, pre-agreed sampling plan across months or a full year if feasible.

- [ ] One or two days only (software demonstration, not a research trend).
- [ ] 2–4 weeks (feasibility of repeated complete handover recording).
- [ ] Longer period, approved plan: ___________________________________________
- [ ] Weekdays included  [ ] Weekends included  [ ] Holidays/seasonal periods considered

**Current application limitation:** annual WISN inputs are stored as current values, not archived by year. Do not overwrite one year's workload with another and call the result a historical series. Preserve each year's source file and obtain supervisor approval for a year-versioned dataset/extension. Operations rounds are date-stamped, but the app does not currently provide annual WISN trend charts or forecasts.

---

## 3. Permission and data-protection checklist

Before receiving any extract, confirm:

- [ ] Which committee/university review is required for the thesis and staff survey.
- [ ] Written hospital permission and data custodian approval.
- [ ] Whether an additional national ethics review is required for this study.
- [ ] Data fields allowed, date range, departments, and intended use.
- [ ] Data are aggregate/de-identified; no patient names, hospital numbers, addresses, or staff names are needed for the app demonstration.
- [ ] Secure storage location, access permissions, retention period, and destruction plan.
- [ ] Whether results may name the hospital or must be reported anonymously.
- [ ] Consent process for staff interviews/usability feedback.

Approval conditions / follow-up: _____________________________________________

___________________________________________________________________________

---

## 4. Questions to ask hospital staff

Ask the appropriate data custodian, nursing administrator/manager, HR/staffing officer, HMIS/statistics officer, and IT/data-protection contact. Do not ask one person to guess figures they do not own.

### A. Departments and nursing units

1. What inpatient nursing units currently operate, and what is each unit's official name?
2. Which services are outpatient? Which Emergency beds are admitted/observation beds rather than walk-in visits?
3. Have unit names, bed functions, or reporting boundaries changed in the requested year?
4. Who can confirm that each workload record belongs to the correct unit?

Notes: ____________________________________________________________________

___________________________________________________________________________

### B. Annual workload volumes and source records

1. Which register/system records the annual volume for each nursing activity?
2. Can the hospital provide totals for the exact 12-month period above? Can it provide monthly totals?
3. Are volumes counts of visits, admissions, procedures, bed-days, or another measure? Define the unit for every measure.
4. Are months or units missing? Were there changes to definitions, forms, software, or reporting practices?
5. Who validates the totals, and can we record the source and extraction date?

Notes/source names: _________________________________________________________

___________________________________________________________________________

### C. Activity and time standards

1. Does the hospital have a current, approved nursing activity/time-standard list?
2. For each standard: what source, version/date, staff cadre, activity definition, and unit of time does it use?
3. Who approved it, and when was it last reviewed?
4. If no standard exists, what local process could validate one (expert panel, time observation, or another approved method)?
5. Which recurring work is direct service, support (for example handover), or additional activity?

Notes: ____________________________________________________________________

___________________________________________________________________________

**Do not fill missing time standards with guesses.** Mark them unavailable and agree with the supervisor how they will be measured or validated.

### D. Available working time (AWT)

1. Which work calendar and reference year should be used?
2. What are scheduled working days, public holidays, annual leave, sick leave, training days, and hours per workday for the relevant nursing cadre?
3. Can the facility provide the written policy/calendar and, where approved, actual leave/training summaries?
4. Do values differ by cadre, contract, or work schedule?

Source/period: ______________________________________________________________

Notes: ____________________________________________________________________

### E. Current nurse staffing

1. Can staffing be reported by department and cadre for the same reference period?
2. What is the difference between sanctioned posts, filled/in-position staff, vacancies, staff temporarily assigned elsewhere, and actual on-duty staff?
3. Is monthly staffing available, or only a count on a particular date?
4. What definition should the thesis use for “current staff,” and who will approve that definition?

Record separately if available: sanctioned ______ filled/in-post ______ vacant ______

Temporary transfers/secondments or other adjustments: _________________________

**For the WISN input, define the in-post staffing figure and date/period explicitly.** The app's `current_staff` is a single annual-planning headcount; it is not an on-duty shift count and does not store cadre breakdowns or yearly history.

### F. Operations handovers and local targets

1. Where is the observed patient census recorded at midnight, 7 AM, and 7 PM?
2. Can the hospital identify admitted/observation-bed patients separately from Emergency walk-ins?
3. How is actual on-duty staffing confirmed at the two handovers? Are float/temporary staff counted, and by which unit?
4. Can the hospital record a complete set of units at every sampled round, including weekends?
5. Does a written unit- and shift-specific nurse-patient target exist? Ask for its source, version, effective dates, approving authority, and any minimum staffing rules.
6. Who is authorised to approve or review an aggregate staffing suggestion?

Notes: ____________________________________________________________________

___________________________________________________________________________

**No default operational ratio is supplied by the application.** Do not activate an unapproved value or present a training-only ratio as hospital policy.

### G. Existing planning workflow and user needs

1. How are annual staffing requirements calculated today? Who prepares and reviews them?
2. What takes the most time or causes the most errors?
3. What decisions should a dashboard/report help the user make?
4. Which roles may enter data, view results, approve standards, or review suggestions?
5. What concerns would staff have about accuracy, workload, confidentiality, or using a digital tool?

Notes: ____________________________________________________________________

___________________________________________________________________________

---

## 5. Department inventory sheet (repeat or attach more copies)

One row per department/unit. Use a short code rather than patient or staff identifiers.

| Unit code/name | Inpatient / OPD / Emergency | WISN included? | Operations included? | Data custodian/source |
|---|---|---|---|---|
| 1. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 2. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 3. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 4. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 5. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 6. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 7. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 8. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 9. __________________________ | ____________________ | __________ | __________ | __________________________ |
| 10. _________________________ | ____________________ | __________ | __________ | __________________________ |

Units excluded and reason: __________________________________________________

___________________________________________________________________________

---

## 6. Annual WISN data collection sheet (repeat for every unit)

Department/unit code: __________________________ Cadre: _____________________

Reference period start: ________________________ end: ________________________

### AWT source and values

| AWT item | Value | Source / note |
|---|---:|---|
| Working days per year | __________ | ________________________________________ |
| Public holidays | __________ | ________________________________________ |
| Annual leave days | __________ | ________________________________________ |
| Sick leave days | __________ | ________________________________________ |
| Training days | __________ | ________________________________________ |
| Working hours per day | __________ | ________________________________________ |
| Computed AWT hours/nurse/year | __________ | ________________________________________ |

### Staffing snapshot/definition

Current in-post nurses used for WISN: __________  Date/period: __________________

Sanctioned: __________  Vacant: __________  Temporary assignments: _____________

Staffing source/definition and approver: ______________________________________

### Workload activity register

| Activity name / definition | Type (HS / support / additional) | Time standard (hours) | Annual volume / unit | Source and validation note |
|---|---|---:|---:|---|
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |
| ___________________________ | _________________________ | ______ | __________________ | __________________________ |

Missing data, exclusions, or changes in definition: ____________________________

___________________________________________________________________________

---

## 7. Operations observation sheet (repeat for each date and complete round)

Observation date: ___________________  Observer code: ________________________

Round is complete for every inpatient unit: [ ] Yes  [ ] No — explain: ____________

| Unit code | Time | Observed inpatient patients | Nurses actually on duty* | Source / note |
|---|---|---:|---:|---|
| __________ | 12:00 AM | ______ | N/A | __________________________ |
| __________ | 7:00 AM | ______ | ______ | __________________________ |
| __________ | 7:00 PM | ______ | ______ | __________________________ |

*Record actual on-duty nurses at 7 AM and 7 PM only. Midnight is census-only. Use one line per unit and time; copy this sheet or add lines so all inpatient units are covered. Do not enter patient or nurse names.

Emergency counted only admitted/observation-bed patients: [ ] Yes  [ ] No  [ ] N/A

OPD excluded from Operations: [ ] Yes  Round/data source: ______________________

Correction/re-entry needed? [ ] No  [ ] Yes — reason: __________________________

---

## 8. Optional staff feedback form — only after required ethics approval/consent

This short form is a **draft for feasibility/usability feedback**, not a validated research scale. Ask your supervisor to approve the instrument and sampling plan. Do not record names. Use: 1 = strongly disagree, 2 = disagree, 3 = neither, 4 = agree, 5 = strongly agree, N/A = not applicable.

Participant code: __________  Role category: __________________  Date: __________

After a brief demonstration or test task, circle one answer for each statement:

1. I could find the department and activity entry screens.  1  2  3  4  5  N/A
2. The department fields and labels were understandable.  1  2  3  4  5  N/A
3. The difference between annual WISN headcount and on-duty staffing was clear.  1  2  3  4  5  N/A
4. The activity types and annual-volume fields were understandable.  1  2  3  4  5  N/A
5. The dashboard results were easy to interpret.  1  2  3  4  5  N/A
6. I could identify the source/version of an Operations target.  1  2  3  4  5  N/A
7. I understand that Operations suggestions require human review.  1  2  3  4  5  N/A
8. I would need training before using this tool for work.  1  2  3  4  5  N/A

Most useful part: ___________________________________________________________

Most confusing/risky part: __________________________________________________

What data or workflow is missing? ____________________________________________

Would you use the tool if approved and supported? [ ] Yes  [ ] No  [ ] Unsure
Why? ______________________________________________________________________

---

## 9. End-of-visit close-out

- [ ] I confirmed the exact annual reference period and data owners.
- [ ] I recorded the source and definition for every requested figure.
- [ ] I distinguished annual WISN staffing from handover on-duty staffing.
- [ ] I confirmed which Emergency counts are admitted/observation beds.
- [ ] I confirmed OPD's WISN/Operations treatment.
- [ ] I have not copied identifiable patient or staff information.
- [ ] I know the next approval step and who must authorize it.
- [ ] I recorded missing data and changes in definitions instead of guessing.

Next person to contact / action / due date:

___________________________________________________________________________

___________________________________________________________________________

Researcher initials: _____________________  Date: ____________________________

## Quick recommendation

For your first substantive WISN analysis, request **one complete, recent 12-month period** of aggregate workload data plus the matching AWT and clearly defined staffing figure. Ask for monthly breakdowns if they are available, but do not request 365 daily rows just to total annual workload. Ask for 3–5 historical years only if your approved research question includes trends and the definitions are comparable. Treat Operations as a separate pilot: a few weeks can test feasibility, while year-round claims require a longer, pre-specified observation plan.
