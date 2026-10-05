#!/usr/bin/env python3
"""
Reformat WISN Core project report to Kathmandu University submission standards.
Outputs: WISN_Core_Report_Formatted.docx
"""

import zipfile, os
from docx import Document
from docx.shared import Pt, Inches, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.table import WD_ALIGN_VERTICAL, WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

# ── Config ────────────────────────────────────────────────────────────────
FONT    = 'Times New Roman'
SRC     = 'WISN Project DRAFT 1.docx'
DEST    = 'WISN_Core_Report_Formatted.docx'
IMG_TMP = '/tmp/wisn_images'

# ── Extract images ────────────────────────────────────────────────────────
os.makedirs(IMG_TMP, exist_ok=True)
with zipfile.ZipFile(SRC, 'r') as z:
    for name in z.namelist():
        if name.startswith('media/') and name.lower().endswith(('.png', '.jpg', '.jpeg')):
            data  = z.read(name)
            fname = os.path.basename(name)
            with open(os.path.join(IMG_TMP, fname), 'wb') as f:
                f.write(data)

IMG3 = os.path.join(IMG_TMP, 'image3.png')   # ER Diagram
IMG4 = os.path.join(IMG_TMP, 'image4.png')   # Flowchart
IMG5 = os.path.join(IMG_TMP, 'image5.png')   # Use Case Diagram

# ── Read original tables ──────────────────────────────────────────────────
old_doc    = Document(SRC)
old_tables = old_doc.tables   # 0..14

# ── Create new document ───────────────────────────────────────────────────
doc = Document()

# ── Page setup ────────────────────────────────────────────────────────────
sec = doc.sections[0]
sec.left_margin      = Inches(1.5)
sec.right_margin     = Inches(1.25)
sec.top_margin       = Inches(1.25)
sec.bottom_margin    = Inches(1.25)
sec.header_distance  = Inches(0.5)
sec.footer_distance  = Inches(0.5)
sec.different_first_page_header_footer = True   # no header/footer on cover

# ── Modify default styles ─────────────────────────────────────────────────
ns = doc.styles['Normal']
ns.font.name              = FONT
ns.font.size              = Pt(12)
ns.paragraph_format.space_before = Pt(0)
ns.paragraph_format.space_after  = Pt(12)
# 1.5 line spacing via XML (lineRule="auto" means "Multiple")
def _set_15_spacing(pf):
    pPr = pf._element.get_or_add_pPr()
    sp  = pPr.get_or_add_spacing()
    sp.set(qn('w:line'),     '360')   # 360 twips = 1.5 × 240
    sp.set(qn('w:lineRule'), 'auto')
_set_15_spacing(ns.paragraph_format)

# Heading style overrides
_H = {'Heading 1': 16, 'Heading 2': 14, 'Heading 3': 13, 'Heading 4': 12}
for hname, sz in _H.items():
    try:
        hs = doc.styles[hname]
        hs.font.name      = FONT
        hs.font.size      = Pt(sz)
        hs.font.bold      = True
        hs.font.color.rgb = RGBColor(0, 0, 0)
        hs.paragraph_format.space_before = Pt(12)
        hs.paragraph_format.space_after  = Pt(6)
        _set_15_spacing(hs.paragraph_format)
    except Exception:
        pass

# ── Helper: apply font on a run ──────────────────────────────────────────
def _font(run, size=12, bold=False, italic=False, underline=False, color=None):
    run.font.name      = FONT
    run.font.size      = Pt(size)
    run.font.bold      = bold
    run.font.italic    = italic
    run.font.underline = underline
    if color:
        run.font.color.rgb = color
    # ensure rFonts in XML
    rPr = run._r.get_or_add_rPr()
    rf  = OxmlElement('w:rFonts')
    rf.set(qn('w:ascii'), FONT); rf.set(qn('w:hAnsi'), FONT); rf.set(qn('w:cs'), FONT)
    existing = rPr.find(qn('w:rFonts'))
    if existing is not None:
        rPr.remove(existing)
    rPr.insert(0, rf)

# ── Chapter heading (18 pt, Heading 1 for TOC) ───────────────────────────
def ch(text, pbr=True):
    p = doc.add_paragraph(style='Heading 1')
    r = p.add_run(text)
    _font(r, size=18, bold=True)
    p.paragraph_format.space_before = Pt(24)
    p.paragraph_format.space_after  = Pt(12)
    p.paragraph_format.page_break_before = pbr
    return p

# ── Section headings ──────────────────────────────────────────────────────
def h1(text):          # x.y — 16 pt, Heading 2
    p = doc.add_paragraph(style='Heading 2')
    r = p.add_run(text); _font(r, size=16, bold=True)
    return p

def h2(text):          # x.y.z — 14 pt, Heading 3
    p = doc.add_paragraph(style='Heading 3')
    r = p.add_run(text); _font(r, size=14, bold=True)
    return p

def h3(text):          # x.y.z.w — 13 pt, Heading 4
    p = doc.add_paragraph(style='Heading 4')
    r = p.add_run(text); _font(r, size=13, bold=True)
    return p

# ── Body paragraph ────────────────────────────────────────────────────────
def body(text, indent=False):
    p = doc.add_paragraph(style='Normal')
    if indent:
        p.paragraph_format.first_line_indent = Inches(0.5)
    r = p.add_run(text); _font(r)
    return p

# ── Bullet point ─────────────────────────────────────────────────────────
def blt(text):
    p = doc.add_paragraph(style='List Bullet')
    r = p.add_run(text); _font(r)
    p.paragraph_format.space_after  = Pt(6)
    p.paragraph_format.left_indent  = Inches(0.5)
    return p

# ── Front-matter heading (centered, 16 pt bold) ──────────────────────────
def fm_heading(text):
    p = doc.add_paragraph(style='Normal')
    r = p.add_run(text)
    _font(r, size=16, bold=True)
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after  = Pt(18)
    return p

# ── Figure/Table caption ─────────────────────────────────────────────────
def cap(text):
    p = doc.add_paragraph(style='Normal')
    r = p.add_run(text); _font(r, italic=True)
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(12)
    return p

# ── Insert centered image ─────────────────────────────────────────────────
def img(path, width=5.5):
    p = doc.add_paragraph(style='Normal')
    r = p.add_run()
    r.add_picture(path, width=Inches(width))
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after  = Pt(4)
    return p

# ── Table of Contents field ───────────────────────────────────────────────
def add_toc():
    p   = doc.add_paragraph(style='Normal')
    run = p.add_run()
    r   = run._r
    fb  = OxmlElement('w:fldChar'); fb.set(qn('w:fldCharType'), 'begin')
    ins = OxmlElement('w:instrText'); ins.set(qn('xml:space'), 'preserve')
    ins.text = ' TOC \\o "1-4" \\h \\z \\u '
    fs  = OxmlElement('w:fldChar'); fs.set(qn('w:fldCharType'), 'separate')
    fe  = OxmlElement('w:fldChar'); fe.set(qn('w:fldCharType'), 'end')
    r.extend([fb, ins, fs])
    r2 = OxmlElement('w:r')
    t  = OxmlElement('w:t')
    t.text = '[Right-click here → "Update Field" to generate the Table of Contents, or press Ctrl+A then F9]'
    r2.append(t); p._p.append(r2)
    r.append(fe)
    return p

# ── Copy table from original doc ─────────────────────────────────────────
def copy_tbl(old_tbl):
    rows = old_tbl.rows
    cols = len(old_tbl.columns)
    t    = doc.add_table(rows=0, cols=cols)
    t.style     = 'Table Grid'
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    avail_w = Inches(6.0)  # usable width between margins
    col_w   = avail_w / cols
    for ri, row in enumerate(rows):
        nr = t.add_row()
        for ci, cell in enumerate(row.cells):
            nc  = nr.cells[ci]
            nc.text = ''
            np_ = nc.paragraphs[0]
            txt = cell.text.strip()
            run = np_.add_run(txt)
            _font(run, size=10, bold=(ri == 0))
            np_.paragraph_format.space_before = Pt(3)
            np_.paragraph_format.space_after  = Pt(3)
            _set_15_spacing(np_.paragraph_format)
    # uniform column widths
    for col in t.columns:
        for c in col.cells:
            c.width = col_w
    # shade header row
    hdr_cells = t.rows[0].cells if t.rows else []
    for c in hdr_cells:
        tc_pr = c._tc.get_or_add_tcPr()
        shd   = OxmlElement('w:shd')
        shd.set(qn('w:val'),   'clear')
        shd.set(qn('w:color'), 'auto')
        shd.set(qn('w:fill'),  'D9D9D9')
        tc_pr.append(shd)
    return t

# ── Page number footer ────────────────────────────────────────────────────
def add_footer_page_num():
    sec    = doc.sections[0]
    footer = sec.footer
    fp     = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
    fp.clear(); fp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = fp.add_run(); _font(run)
    fb  = OxmlElement('w:fldChar'); fb.set(qn('w:fldCharType'), 'begin')
    ins = OxmlElement('w:instrText'); ins.set(qn('xml:space'), 'preserve'); ins.text = ' PAGE '
    fe  = OxmlElement('w:fldChar'); fe.set(qn('w:fldCharType'), 'end')
    run._r.extend([fb, ins, fe])

# ── Simple page break (within a section) ─────────────────────────────────
def page_break():
    doc.add_page_break()

# ── Section break (terminates the current section) ────────────────────────
def make_section_break(fmt='decimal', start=1):
    """Ends the current section. fmt: 'decimal' | 'upperRoman' | 'lowerRoman'."""
    p   = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after  = Pt(0)
    pPr = p._p.get_or_add_pPr()
    spr = OxmlElement('w:sectPr')
    te  = OxmlElement('w:type');     te.set(qn('w:val'), 'nextPage'); spr.append(te)
    pgSz = OxmlElement('w:pgSz');    pgSz.set(qn('w:w'), '12240'); pgSz.set(qn('w:h'), '15840'); spr.append(pgSz)
    pgMar = OxmlElement('w:pgMar')
    pgMar.set(qn('w:top'),    '1800'); pgMar.set(qn('w:right'),  '1800')
    pgMar.set(qn('w:bottom'), '1800'); pgMar.set(qn('w:left'),   '2160')
    pgMar.set(qn('w:header'), '720');  pgMar.set(qn('w:footer'), '720')
    pgMar.set(qn('w:gutter'), '0');    spr.append(pgMar)
    pgNum = OxmlElement('w:pgNumType')
    pgNum.set(qn('w:fmt'), fmt); pgNum.set(qn('w:start'), str(start)); spr.append(pgNum)
    pPr.append(spr)
    return p

# ── Add centered page-number footer to a section ──────────────────────────
def _add_page_footer(section, field=' PAGE '):
    """field is the raw Word field instruction, e.g. ' PAGE ' or ' PAGE \\* ROMAN '."""
    footer = section.footer
    footer.is_linked_to_previous = False
    fp = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
    fp.clear(); fp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = fp.add_run(); _font(run)
    fb  = OxmlElement('w:fldChar'); fb.set(qn('w:fldCharType'), 'begin')
    ins = OxmlElement('w:instrText'); ins.set(qn('xml:space'), 'preserve'); ins.text = field
    fe  = OxmlElement('w:fldChar'); fe.set(qn('w:fldCharType'), 'end')
    run._r.extend([fb, ins, fe])

def blank():
    p = doc.add_paragraph(style='Normal')
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after  = Pt(0)
    return p

# ═══════════════════════════════════════════════════════════════════════════
# COVER PAGE
# ═══════════════════════════════════════════════════════════════════════════
def ctext(text, size=12, bold=False, underline=False, space_after=6):
    p = doc.add_paragraph(style='Normal')
    r = p.add_run(text); _font(r, size=size, bold=bold, underline=underline)
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(0)
    p.paragraph_format.space_after  = Pt(space_after)
    return p

ctext('KATHMANDU UNIVERSITY',           size=18, bold=True, underline=True, space_after=4)
ctext('DHULIKHEL, NEPAL',               size=14, bold=True, space_after=36)
ctext('A PROJECT REPORT ON:',           size=14, bold=True, underline=True, space_after=12)
ctext('WISN CORE: A WEB-BASED HOSPITAL HUMAN RESOURCE MANAGEMENT PROTOTYPE',
      size=14, space_after=36)
ctext('SUBMITTED TO',                   size=14, bold=True, underline=True, space_after=12)
ctext('Asst. Prof. Niranjan Rimal',     size=12, space_after=4)
ctext('DEPARTMENT OF HEALTH INFORMATICS, KATHMANDU UNIVERSITY', size=12, space_after=36)
ctext('SUBMITTED BY',                   size=14, bold=True, underline=True, space_after=12)
ctext('Suzata Pandit',                  size=12, space_after=4)
ctext('Department of Health Informatics, Kathmandu University', size=12, space_after=0)

make_section_break(fmt='decimal', start=1)   # Ends Section 1 (cover — no page number)

# ═══════════════════════════════════════════════════════════════════════════
# ABSTRACT  (Section 2 begins — Roman numerals I, II, III …)
# ═══════════════════════════════════════════════════════════════════════════
fm_heading('ABSTRACT')
body("WISN Core is a web-based hospital human resource management prototype designed to implement the World Health Organization's Workload Indicators of Staffing Need (WISN) methodology in a Nepalese hospital context. It provides hospital administrators and department heads with a real-time, evidence-based staffing analysis tool that converts raw workload data and statutory leave entitlements into quantified nurse staffing requirements.")
body("The system is built on the Laravel 10 PHP framework following the Model-View-Controller architectural pattern, with user authentication provided by Laravel Breeze and chart rendering by Chart.js. Data persistence uses a MySQL relational database normalised to Third Normal Form, with PDF report generation handled by the DomPDF library.")
body("The prototype quantifies clinical bottlenecks through three WISN workload components — health service activities, support activities, and additional activities — and derives each department's Available Working Time from six statutory parameters drawn from the Nepal Labour Act 2074 and the Nepal Health Service Regulations. The system is pre-populated with four empirically grounded Nepalese hospital departments (ICU, Emergency Department, General Medical Ward, and Surgical Ward) and twenty-eight workload activities derived from WHO standard time values and national health facility survey data.")
body("This project demonstrates that rigorous, empirically-seeded health informatics prototypes can bridge the gap between digital healthcare planning and evidence-based workforce management within a developing-country health system.")
p = doc.add_paragraph(style='Normal')
rb = p.add_run('Keywords: '); _font(rb, bold=True)
rv = p.add_run('WISN, WHO, HRH, Laravel, Nursing Workload, Staffing, Nepal, Health Informatics, Available Working Time')
_font(rv)

page_break()

# ═══════════════════════════════════════════════════════════════════════════
# ACKNOWLEDGEMENT
# ═══════════════════════════════════════════════════════════════════════════
fm_heading('ACKNOWLEDGEMENT')
body("We would like to express our sincere gratitude to the Department of Health Informatics for providing us with the academic foundation and institutional support necessary to undertake this project.")
body("We are deeply grateful to our project supervisor, Asst. Prof. Niranjan Rimal, for constant guidance, encouragement, and methodological support throughout the duration of this project. His expertise in health informatics provided invaluable direction at every stage of development.")
body("We also extend our thanks to the World Health Organization for making the WISN methodology publicly available, and to the Ministry of Health and Population, Nepal, for publishing the 2021 Nepal Health Facility Survey, which provided the empirical grounding for this prototype.")

page_break()

# ═══════════════════════════════════════════════════════════════════════════
# TABLE OF CONTENTS
# ═══════════════════════════════════════════════════════════════════════════
fm_heading('TABLE OF CONTENTS')
add_toc()

page_break()

# ═══════════════════════════════════════════════════════════════════════════
# LIST OF FIGURES
# ═══════════════════════════════════════════════════════════════════════════
fm_heading('LIST OF FIGURES')
figures = [
    ('Figure 3.1', 'Entity-Relationship (ER) Diagram'),
    ('Figure 3.2', 'System Flowchart'),
    ('Figure 3.3', 'Use Case Diagram'),
]
for fn, fd in figures:
    p = doc.add_paragraph(style='Normal')
    r = p.add_run(f'{fn}:\t{fd}'); _font(r)
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.tab_stops.add_tab_stop(Inches(1.6))

page_break()

# ═══════════════════════════════════════════════════════════════════════════
# LIST OF TABLES
# ═══════════════════════════════════════════════════════════════════════════
fm_heading('LIST OF TABLES')
tables_list = [
    ('Table 2.1',  'Comparative Analysis of Related Systems'),
    ('Table 4.1',  'Technology Stack'),
    ('Table 4.2',  'Demonstration Departments Summary'),
    ('Table 4.3',  'Sample ICU Activity Data'),
    ('Table 5.1',  'Available Working Time (AWT) Breakdown – Nepal Standard'),
    ('Table 5.2',  'Labour Standards Comparison (Nepal Labour Act 2074)'),
    ('Table 5.3',  'Department-Level WISN Results'),
    ('Table 5.4',  'Facility-Level Summary'),
    ('Table 5.5',  'WISN Ratio Status Distribution'),
    ('Table 5.6',  'Digital vs. Manual Calculation Comparison'),
    ('Table 5.7',  'Comparison Against Nepal Staffing Norms (NHFS 2021)'),
    ('Table 5.8',  'Test Cases – Department and Activity Data'),
    ('Table 5.9',  'Test Cases – WISN Calculation Accuracy'),
    ('Table 5.10', 'Test Cases – Access Control'),
    ('Table 5.11', 'Test Cases – Report Output Consistency'),
]
for tn, td in tables_list:
    p = doc.add_paragraph(style='Normal')
    r = p.add_run(f'{tn}:\t{td}'); _font(r)
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.tab_stops.add_tab_stop(Inches(1.6))

make_section_break(fmt='upperRoman', start=1)  # Ends Section 2 (front matter — Roman numerals)

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 1: INTRODUCTION  (Section 3 begins — Arabic numerals 1, 2, 3 …)
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 1: Introduction', pbr=False)   # section break already starts a new page

h1('1.1 Overview')
body("WISN Core is a single-facility web-based prototype designed to implement the World Health Organization's Workload Indicators of Staffing Need (WISN) methodology in a Nepalese hospital context. The system provides hospital administrators and department heads with a structured, evidence-based tool to quantify nursing staffing requirements from recorded workload data and statutory leave entitlements.")
body("The impetus for this project lies in the well-documented human resource crisis within Nepal's public health system. The 2021 Nepal Health Facility Survey (NHFS) found median nursing staffing levels of 9.9 nurses at federal and provincial hospitals — substantially below WHO-recommended benchmarks — while occupational burnout studies across Kathmandu Valley hospitals report prevalence rates between 26% and 40% among clinical nurses. Despite this documented crisis, no locally adapted digital tool exists to systematically quantify the staffing gap using the internationally validated WISN framework.")
body("The application is built using the Laravel 10 PHP framework following the Model-View-Controller (MVC) architectural pattern. It uses Laravel Breeze for user authentication with email verification, Chart.js for data visualisation on a facility-wide dashboard, and DomPDF for downloadable PDF report generation. Data is persisted in a MySQL database normalised to Third Normal Form.")

h1('1.2 Problem Statement')
body("The Nepalese public healthcare system faces an acute and worsening human resource crisis. The gap between the number of nurses currently deployed in hospitals and the number scientifically required to handle the actual patient workload is significant, measurable, and consequential. This gap drives clinical errors, occupational burnout, and ultimately poor patient outcomes.")
body("Despite the existence of an internationally validated methodology for quantifying this exact problem — the WHO WISN framework — there is no accessible digital tool that allows Nepalese hospital administrators to implement WISN calculations in real time. Specifically:")
blt("There is no accessible digital tool for Nepalese hospital administrators to implement WHO WISN calculations in real time.")
blt("Staffing decisions are made without a quantitative basis, ignoring the Available Working Time (AWT) constraints defined by the Nepal Labour Act 2074.")
blt("The gap between the MoHP Minimum Service Standards and actual NHFS-documented staffing medians is never systematically measured at the facility level.")
blt("Occupational burnout risk is reactive — identified only after clinical incidents — rather than predictive.")

h1('1.3 Aims and Objectives')
body("The primary aim of WISN Core is to provide a mathematically rigorous, empirically anchored digital implementation of the WHO WISN staffing methodology, specifically adapted for the Nepalese hospital context. The specific objectives are:")
blt("To implement the full three-component WHO WISN staffing calculation (Health Service Activities, Support Activities, and Additional Activities) within a web-based application.")
blt("To derive and enforce the Available Working Time (AWT) parameter from the Nepal Labor Act 2074 (2017) and Nepal Health Service Regulations.")
blt("To seed the system with empirically validated staffing baselines drawn from the 2021 Nepal Health Facility Survey, clinical literature, and WHO standard time values.")
blt("To compute and display a WISN staffing ratio for each department and a facility-level aggregate ratio, with automated status classification.")
blt("To generate downloadable, DomPDF-rendered PDF reports suitable for submission to hospital administration or MoHP planning bodies.")
blt("To provide a secure, role-protected interface via Laravel Breeze authentication and email verification.")

h1('1.4 Scope of Study')
body("The project is scoped as a single-facility web prototype targeting hospital administrators, department heads, and human resources managers. The scope includes:")
blt("Department management: creation, editing, and deletion of hospital departments with individually configurable AWT parameters.")
blt("Workload activity management: definition of clinical tasks across the three WISN categories with WHO-standard time values.")
blt("WISN calculation engine: real-time computation of the Staffing Requirement, Category Allowance Factor (CAF), and WISN Ratio for each department.")
blt("Dashboard visualisation: facility-wide summary cards and per-department status indicators rendered via Chart.js.")
blt("Report generation: downloadable PDF reports formatted for administrative submission.")
body("The system is explicitly scoped to a single facility and does not incorporate multi-hospital federations, payroll integration, shift scheduling, or electronic health record connectivity.")

h1('1.5 Feasibility Study')
h2('1.5.1 Technical Feasibility')
body("The project is built entirely on mature, open-source technologies with extensive community support. Laravel 10 is a production-grade PHP framework used globally in healthcare and enterprise applications. MySQL provides ACID-compliant data persistence. Chart.js and DomPDF are widely deployed front-end and document-generation libraries respectively. All components have been successfully integrated in the prototype, confirming technical feasibility.")

h2('1.5.2 Economic Feasibility')
body("The total development cost of this project is restricted strictly to developer time. As an academic endeavor, development tooling (VS Code, Git, Composer, npm) and hosting (local PHP development server) are free. All software dependencies are distributed under open-source licences (MIT or equivalent).")
body("While the immediate development costs are virtually nil, future production deployment on platforms such as AWS or DigitalOcean would involve modest infrastructure costs estimated at USD 10–30 per month for a single-facility deployment. These costs are well within reach of Nepalese district or provincial hospitals.")

h2('1.5.3 Operational Feasibility')
body("The system is designed for users with standard web literacy. The interface follows established patterns from major hospital information systems, ensuring a shallow learning curve. The step-by-step activity management workflow mirrors the natural structure of the WISN methodology, meaning that domain-expert users (department heads) will find the interface intuitive without prior training.")

h2('1.5.4 Legal Feasibility')
body("All software components are distributed under open-source licences (MIT or equivalent) that permit academic and non-commercial use without restriction. The AWT parameters used by the system are derived directly from the Nepal Labour Act 2074 and Nepal Health Service Regulations — publicly available legislation — and do not involve any proprietary data. No patient-identifiable information is stored or processed.")

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 2: LITERATURE REVIEW
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 2: Literature Review')

h1('2.1 The WHO WISN Methodology')
body("The Workload Indicators of Staffing Need (WISN) methodology is a facility-based workforce planning tool developed by the World Health Organization to help health facility managers determine the number of health workers needed to cope with the workload at their facilities [2]. Unlike simple staffing ratios, WISN calculates staff requirements from the bottom up, starting with actual workload data and facility-specific time parameters.")
body("The WISN methodology has been implemented and validated globally. For example, a WISN implementation study in Namibia resulted in a 17% increase in the nursing workforce following evidence-based reallocation guided by WISN results [11]. In Greece, WISN was applied to determine midwifery staffing requirements across public hospitals, confirming its adaptability to diverse healthcare systems [12]. In Nepal, the Ministry of Health and Population (MoHP) has endorsed the WISN framework as a component of its Human Resources for Health strategic planning, although no nationally scaled digital implementation exists [1].")
body("According to the WHO WISN User's Manual [2], implementing the methodology involves eight distinct mathematical steps:")
blt("Identify the health worker cadre and facility type.")
blt("Calculate available working time — determine total working days per year and subtract all non-working days including public holidays, annual leave, sick leave, and training days.")
blt("Define workload components — capture all main activities performed by the cadre.")
blt("Establish activity standards — determine the time required to perform each workload component according to professional standards.")
blt("Calculate standard workloads — divide available working time by the activity standard for each component.")
blt("Calculate allowance factors — account for additional activities and support functions.")
blt("Determine required number of staff — divide actual annual workload by standard workload and add allowance factors.")
blt("Calculate the WISN ratio — current staff divided by required staff.")

h1('2.2 The 2021 Nepal Health Facility Survey')
body("The 2021 Nepal Health Facility Survey (NHFS), implemented by the MoHP alongside New ERA and ICF, is the most comprehensive facility-based assessment of Nepal's healthcare system [1]. The survey covered 1,399 health facilities across all seven provinces, collecting data on staffing, infrastructure, service availability, and quality indicators [17].")
body("The NHFS documented a critical shortage of human resources across almost all public facilities [1]. Most notably, the survey found a median of 9.9 nurses at federal and provincial-level hospitals, dropping to 4.0 at local-level hospitals [17]. Emergency departments reported a median of 2 nurses per shift — a figure far below what WISN calculations indicate as necessary for the documented patient volumes.")

h1('2.3 Nepalese Labor and Health Service Legislation')
body("The Nepal Labor Act 2074 (2017) modernized the nation's employment guidelines by replacing the outdated 1992 Act [3]. Under the 2074 Act, the standard working week for public sector employees is eight hours per day, five to six days per week, with a mandated minimum of 13 public holidays per year (14 for female employees), 12 days of sick leave, and 18 days of annual leave.")
body("For public sector healthcare workers, the Nepal Health Service Act 2053 (1997) [4] and its accompanying Nepal Health Service Regulations [5] define cadre-specific entitlements. The WISN Core prototype derives its default AWT calculation directly from these legislative sources, using 260 working days, 13 public holidays, 18 annual leave days, 12 sick days, and 5 training days — producing an AWT of 1,696 hours per nurse per year.")

h1('2.4 Hospital Workloads and Patient Volumes in the Kathmandu Valley')
body("Empirical studies of major Kathmandu teaching hospitals provide the demand-side constants critical to WISN Core's test seeding. The Tribhuvan University Teaching Hospital (TUTH) Emergency Department handles approximately 43,185 patient visits per year, based on published utilisation studies [7]. An audit of the observation ward supporting the emergency room at a Kathmandu tertiary care hospital confirms comparable demand-side pressures at similar institutions [10].")
body("The admission rate from emergency departments at Nepalese tertiary centers has been empirically established at 40.83%, providing the basis for inpatient volume estimates used to seed the General Medical Ward and ICU activities in the prototype [7].")

h1('2.5 Occupational Burnout in Nepalese Healthcare')
body("The consequences of persistent staffing deficits are extensively documented in recent Nepalese clinical literature. A descriptive cross-sectional study of nurses and doctors at a tertiary care government hospital in Kathmandu found significant burnout prevalence across emotional exhaustion and depersonalisation dimensions, with staffing shortfalls identified as a contributing factor [9]. A more recent study examining burnout and sleep problems among nurses at a tertiary hospital in Kathmandu confirmed that workload-driven pressure — directly correlated with understaffing — is a primary driver of burnout outcomes in Nepalese clinical settings [8]. These findings are consistent across ICU and emergency nursing contexts — the same cadres identified as critically understaffed by WISN calculations in this prototype.")

h1('2.6 Related Systems and Comparative Analysis')
body("A comparative analysis of existing systems contextualises WISN Core's design decisions:")
cap('Table 2.1: Comparative Analysis of Related Systems')
copy_tbl(old_tables[0])
body("The key differentiator of WISN Core over the WHO's own Excel-based tool is the automation of the mathematical pipeline: users enter raw workload counts and leave parameters and the system computes all intermediate values — Standard Workload, CAF, AAF FTE — without manual calculation. This eliminates the arithmetic errors and version control problems inherent in spreadsheet-based implementations.")

h1('2.7 Research Gap')
body("While the WHO WISN methodology provides an objective, workload-driven algorithm for human resources for health (HRH) planning, its digital implementation in Nepal remains fragmented and inaccessible. This project directly addresses three dimensions of this gap.")
body("First, existing workforce scheduling and HR systems (e.g., HealthRoster, Staffplan) are proprietary, high-cost, and optimised for Western shift-scheduling problems rather than the WHO WISN staffing-needs framework. Second, the WHO's own Excel-based WISN tool requires manual input of all intermediate calculations, making it error-prone and unsuitable for non-specialist administrators. Third, no Nepal-specific digital implementation integrates the statutory AWT parameters from the Nepal Labour Act 2074 or uses NHFS-derived baseline figures.")

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 3: SYSTEM DESIGN
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 3: System Design')

h1('3.1 ER Diagram')
body("The relational database schema is normalized to Third Normal Form (3NF) to ensure data integrity and query performance in a multi-user environment. The ER diagram below illustrates the relationships between the core entities: Users, Departments, and Workload Activities.")
img(IMG3, width=5.5)
cap('Figure 3.1: Entity-Relationship (ER) Diagram')

h1('3.2 Flowchart')
body("The flowchart describes the user journey from unauthenticated access through to workload configuration and PDF generation. It illustrates the decision points at authentication, email verification, and data entry, as well as the calculation pipeline triggered on dashboard load.")
img(IMG4, width=5.5)
cap('Figure 3.2: System Flowchart')

h1('3.3 Use Case Diagram')
body("To systematically map out the interactions within WISN Core's monolithic web layer, a structural Use Case Diagram is established. The diagram identifies the primary actor (Authenticated Hospital Administrator) and all system use cases, including department management, activity management, dashboard viewing, and report generation.")
img(IMG5, width=5.5)
cap('Figure 3.3: Use Case Diagram')

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 4: IMPLEMENTATION AND DISCUSSION
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 4: Implementation and Discussion')

h1('4.1 Development Approach')
body("The system was built using an iterative, phase-based approach. Development progressed through four stages: designing the data model and running migrations; building the WISN calculation engine and unit-verifying it against manual calculations; constructing the front-end interface (dashboard, department forms, activity management); and finally adding PDF generation, seeder data, and authentication hardening.")

h1('4.2 Technology Stack')
body("The application was built on a modern, open-source web stack chosen for its stability, security, and strong documentation ecosystem.")
cap('Table 4.1: Technology Stack')
copy_tbl(old_tables[1])
body("All components use permissive open-source licenses and are actively maintained, which reduces long-term maintenance risk and makes the project suitable for academic or non-commercial hospital deployment. Full technical documentation is available for each component: Laravel 10 [13], barryvdh/laravel-dompdf [14], Chart.js 4.4 [15], and Tailwind CSS v3 [16].")

h1('4.3 Database Design')
body("The database was normalised to third normal form and is organised around two core tables, in addition to the standard user management tables provided by Laravel Breeze:")
body("Departments — stores each department's identity (name, type) along with six separate fields that make up its statutory AWT: working_days_per_year, public_holidays, annual_leave_days, sick_leave_days, training_days, and working_hours_per_day. A computed Eloquent attribute automatically derives the total AWT from these six fields whenever required.")
body("Workload Activities — stores each recorded task for a department: its name, its category (Health Service, Support, or Additional), its time standard (hours), and its annual volume count (for Health Service and Additional activities only).")

h1('4.4 Available Working Time (AWT) Calculation')
body("Rather than storing AWT as a fixed number, the system calculates it automatically whenever it is needed, directly from the six statutory component fields on the departments table:")
body("AWT (hours/year) = (Working Days per Year − Public Holidays − Annual Leave Days − Sick Leave Days − Training Days) × Working Hours per Day")
body("For the Nepal-standard default configuration used throughout testing:")
body("AWT = (260 − 13 − 18 − 12 − 5) × 8 = 212 × 8 = 1,696 hours per nurse per year")
body("This figure aligns with the Nepal Labour Act 2074 framework and was cross-checked against the Nepal Health Facility Survey 2021 documentation.")

h1('4.5 WISN Calculation Logic')
body("The core staffing calculation follows the WHO Workload Indicators of Staffing Need (WISN) methodology in four sequential steps:")
body("Step 1 — Health Service Staff Requirement: For every Health Service activity, a standard workload is first established, then divided into the recorded annual volume:")
blt("Standard Workload = AWT ÷ Time Standard (hours per activity)")
blt("Required FTE = Annual Volume ÷ Standard Workload")
body("Example (ICU — continuous monitoring): Standard Workload = 1,696 ÷ 0.33 = 5,139.39; Required FTE = 17,520 ÷ 5,139.39 = 3.41")
body("Step 2 — Support Time Allowance: Support activities (tasks such as handovers and ward rounds that are necessary but not patient-volume-driven) are expressed as a fraction of the working day and aggregated into a Category Allowance Factor (CAF):")
blt("Support Fraction (per activity) = Time Standard (hours) ÷ Working Hours per Day")
blt("CAF = 1 ÷ (1 − Σ Support Fractions)")
body("The working hours figure used here comes from each department's own settings rather than a fixed value, so departments with non-standard shift lengths are handled correctly.")
body("Step 3 — Additional Activity Allowance: Additional activities (occasional, non-routine tasks) are converted directly into FTE:")
blt("AAF Hours = Annual Volume × Time Standard (hours)")
blt("AAF FTE = AAF Hours ÷ AWT")
body("Step 4 — Final Staffing Requirement and Ratio:")
blt("Total Required Staff = (Health Service FTE × CAF) + AAF FTE")
blt("WISN Ratio = Current Staff ÷ Total Required Staff")
body("The resulting ratio is classified using WHO's standard thresholds: below 0.90 is Critical, below 1.00 is Borderline, exactly 1.00 is Adequate, and above 1.00 is Surplus.")
body("A safeguard was built into this step: if the combined support fractions reach or exceed 1.0, the CAF formula would otherwise produce a division-by-zero or negative value. In this edge case the system flags the department rather than applying an invalid CAF.")

h1('4.6 Application Structure and Access Control')
body("The application follows a standard separation between data handling, business logic, and presentation. A dedicated calculation service (WisnCalculatorService) encapsulates all WISN mathematics, keeping the controllers thin and making the calculation logic independently testable.")
body("Every page in the system, including the dashboard, department management, activity management, and reporting, is only accessible to users who have both authenticated (logged in) and verified their email address. Unauthenticated users are redirected to the login page; authenticated but unverified users are redirected to the email verification prompt.")
body("Two form-level safeguards were added for data quality: departments cannot be saved if their leave and training days would push the AWT below zero, and support activities that together exceed a full working day trigger a CAF boundary warning rather than producing an invalid calculation.")

h1('4.7 User Interface')
body("The interface was designed around a step-by-step workflow that mirrors the logical order of the WISN methodology, so that hospital administrators can follow the same sequence they would use in a manual WISN assessment.")
body("Key screens include:")
body("Dashboard — three summary cards (Total Current Staff, Total Required Staff, Facility WISN Ratio), a colour-coded bar chart comparing current versus required staff per department, and a WISN status pie chart.")
body("Department Forms — a two-part form covering basic department information and the six AWT fields, with a live preview that shows the calculated AWT as the user adjusts the leave fields.")
body("Activity Management — a four-step progress indicator that tracks whether Health Service and Support activities have been entered, whether the WISN ratio has been calculated, and whether the department is ready for reporting.")
body("PDF Report — a downloadable summary containing facility-level totals, a per-department results table, and a breakdown of the AWT calculation for each department.")
body("All ratio indicators use a consistent colour scheme (grey, red, yellow, green, blue) throughout the dashboard and PDF report.")

h1('4.8 Demonstration Data')
body("To allow the system to be evaluated with realistic figures, it was pre-populated with four Nepalese hospital departments and twenty-eight workload activities derived from published sources.")
cap('Table 4.2: Demonstration Departments Summary')
copy_tbl(old_tables[2])
body("Sample ICU activity data:")
cap('Table 4.3: Sample ICU Activity Data')
copy_tbl(old_tables[3])
body("Because this demonstration dataset is designed to reset and repopulate the database on each run, it is intended strictly for development and evaluation purposes and must not be used in a live production environment with real patient data.")

h1('4.9 Challenges Faced and How They Were Addressed')
blt("Keeping AWT consistent: Deriving AWT automatically from its six components, rather than storing it separately, removed the risk of stale values after an edit. This was implemented as an Eloquent computed attribute that recalculates on every access.")
blt("Validating leave allowances against working days: A cross-check was added to ensure the sum of holidays, leave, sick days, and training days never exceeds the total working days per year, preventing an AWT of zero or below.")
blt("CAF edge case: When total support time reaches or exceeds a full working day's equivalent, the standard CAF formula breaks down. A guard clause was added to detect this condition and flag the department rather than producing an invalid result.")
blt("Report styling constraints: The PDF generation library used does not support the same styling approach as the rest of the application. The PDF view was written with fully inline CSS, avoiding any external stylesheets.")
blt("Justifying the baseline figures: The default AWT and staffing assumptions used for testing were not chosen arbitrarily; each was traced to a specific legislative or survey source and documented in the project's methodology section.")

h1('4.10 Outputs Achieved')
body("User-facing features:")
blt("Registration with email verification, and secure login.")
blt("A facility-wide dashboard showing overall staffing ratio, department status, and comparison charts.")
blt("Department management with a live AWT preview.")
blt("Activity management with a guided, step-by-step progress indicator.")
blt("Downloadable PDF staffing report.")
blt("A static reference page explaining the WISN methodology.")
blt("Basic profile management (updating account details, changing password, account deletion).")
body("Calculation engine outcomes:")
blt("Real-time WISN ratio calculation across all three activity categories.")
blt("Automatic status classification (Critical / Borderline / Adequate / Surplus).")
blt("Facility-wide aggregate ratio combining all departments.")
blt("A detailed, per-activity breakdown of staffing contribution, available on both the dashboard and the PDF report.")

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 5: ANALYSIS AND EVALUATION
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 5: Analysis and Evaluation')

h1('5.1 Data Analysis')
body("The system was evaluated using the four seeded departments and their twenty-eight associated workload activities described in Chapter 4. The analysis covers three dimensions: the correctness of the AWT calculation against statutory baselines, the accuracy of WISN staffing calculations against manually verified figures, and a high-level comparison against NHFS national staffing norms.")
body("Available Working Time (AWT) — All Departments, Nepal Standard")
body("All departments use the default AWT configuration based on the Nepal Labour Act 2074:")
cap('Table 5.1: Available Working Time (AWT) Breakdown – Nepal Standard')
copy_tbl(old_tables[4])
body("Data Source Comparison")
body("The Nepal Health Facility Data Summary provides additional context for the labour standards used as inputs:")
cap('Table 5.2: Labour Standards Comparison (Nepal Labour Act 2074)')
copy_tbl(old_tables[5])
body("Nepal Health Facility Survey (NHFS, 2021) reports median staffing baselines of 9.9 nurses at federal/provincial hospitals and 4.0 at local-level hospitals, providing national context for the seeded department staffing levels (6–11 nurses).")

h1('5.2 Results')
body("The system produced the following WISN staffing calculations for the four seeded departments:")
body("Department-Level Results")
cap('Table 5.3: Department-Level WISN Results')
copy_tbl(old_tables[6])
body("Facility-Level Summary")
cap('Table 5.4: Facility-Level Summary')
copy_tbl(old_tables[7])
body("WISN Ratio Interpretation — Status Distribution")
cap('Table 5.5: WISN Ratio Status Distribution')
copy_tbl(old_tables[8])

h1('5.3 Comparison')
body("Digital vs. Manual WISN Calculation")
body("The system's calculations were verified against manual WHO-standard calculations performed independently in a spreadsheet, using the same input data as the seeded departments.")
cap('Table 5.6: Digital vs. Manual Calculation Comparison')
copy_tbl(old_tables[9])
body("All digital calculations matched the manual calculations with zero discrepancy, confirming the arithmetic accuracy of the implementation.")
body("Comparison Against Nepal Staffing Norms")
cap('Table 5.7: Comparison Against Nepal Staffing Norms (NHFS 2021)')
copy_tbl(old_tables[10])
body("Direct comparison at the facility level is not possible, since the NHFS reports facility-wide medians while WISN calculates per-department requirements. However, the seeded current staff values (6–11 per department) fall within the range reported by the NHFS for provincial and local-level hospitals, confirming that the demonstration data is representative of Nepalese hospital conditions.")

h1('5.4 Discussion of Findings')
body("Critical staffing gaps in high-acuity departments: The ICU (WISN ratio 0.66) and Emergency Department (0.84) show critical understaffing. These departments handle the highest-acuity patients yet have the largest gap between current staff and WISN-calculated requirements — a finding consistent with the NHFS observation that emergency departments reported the sharpest staffing deficits.")
body("Support activity impact: CAF values range from 1.143 to 1.200, meaning support activities (handover, rounds, documentation) account for 14%–20% of each nurse's effective working time. This non-trivial overhead confirms that a staffing model based purely on patient volume would systematically underestimate requirements.")
body("Uneven staffing distribution: Despite the General Medical Ward showing a surplus (ratio 1.05), the facility as a whole faces a borderline staffing situation (ratio 0.90). This pattern suggests that staff may be allocated across departments based on historical precedent rather than workload analysis.")
body("Methodology validation: The exact match between digital and manual calculations validates the correctness of the implementation. There is no discrepancy between the formula as specified in the WHO WISN manual and as coded in WisnCalculatorService.")
body("Workforce planning implications: A facility-level WISN ratio of 0.90 indicates a borderline overall staffing situation. Without intervention, continued growth in patient volumes — projected at 5–8% annually for Kathmandu Valley hospitals — will push this ratio into the critical range within two to three years.")
body("Limitations: These results are based on simulated data derived from published literature and WHO standard time values. Actual facility data would be needed to generate actionable staffing plans. Additionally, the prototype currently models only nursing staff; extending to other cadres (physicians, support workers) would require additional workload categories.")

h1('5.5 Testing Approach')
body("Wherever possible, test inputs were drawn from published, verifiable sources rather than arbitrary placeholder values — this ensures test results are both technically valid and contextually meaningful.")

h1('5.6 Test Cases – Department and Activity Data')
body("Department and activity inputs were tested for both realism and validity.")
cap('Table 5.8: Test Cases – Department and Activity Data')
copy_tbl(old_tables[11])

h1('5.7 Test Cases – WISN Calculation Accuracy')
body("These test cases verify the mathematical correctness of the calculation logic against known, pre-calculated inputs.")
cap('Table 5.9: Test Cases – WISN Calculation Accuracy')
copy_tbl(old_tables[12])

h1('5.8 Test Cases – Access Control')
body("Access control was tested to confirm that WISN data can only be viewed or modified by legitimate, verified users.")
cap('Table 5.10: Test Cases – Access Control')
copy_tbl(old_tables[13])

h1('5.9 Test Cases – Report Output Consistency')
body("The downloadable PDF report was checked against the dashboard to confirm the two outputs never disagree.")
cap('Table 5.11: Test Cases – Report Output Consistency')
copy_tbl(old_tables[14])

h1('5.10 Summary')
body("Across all testing categories, the system's WISN calculations matched independently verified, manually calculated values with zero discrepancy. Access control behaved as designed across all four test scenarios. Report output was consistent with dashboard data in all three checked parameters. The one boundary condition tested (CAF with excessive support time) was handled gracefully without producing an invalid result.")

# ═══════════════════════════════════════════════════════════════════════════
# CHAPTER 6: CONCLUSION
# ═══════════════════════════════════════════════════════════════════════════
ch('Chapter 6: Conclusion')

h1('6.1 Summary and Contributions')
body("WISN Core demonstrates that a rigorous, empirically anchored health informatics web application can be successfully built within an academic project timeframe using open-source technologies. The prototype fully implements the WHO three-component WISN staffing calculation, including the CAF boundary safeguard, derives Available Working Time from six statutory inputs aligned to the Nepal Labour Act 2074, and produces results that match manually verified calculations with zero discrepancy.")
body("The key technical contribution of the project is its WISN calculation engine, which automates the three-component WISN pipeline — eliminating the manual arithmetic errors inherent in spreadsheet implementations — and surfaces the results on an interactive dashboard and downloadable PDF report accessible through a secure web interface.")
body("The application's six-field AWT breakdown interface provides a legally defensible capacity-planning parameter by allowing administrators to configure leave entitlements in accordance with current Nepalese legislation, rather than relying on a single hard-coded assumption.")
body("Beyond its immediate utility as a hospital management tool, WISN Core establishes a replicable approach for health informatics prototyping: using empirically sourced seeder data to validate calculations before deployment, deriving statutory parameters from published legislation, and building against internationally validated methodologies rather than ad hoc rules.")
body("This project successfully developed a web-based nursing staffing tool that implements WHO WISN methodology in an accessible, Nepal-specific digital format. The prototype fills a concrete gap in the Nepalese health system's digital infrastructure: a locally adapted, publicly accessible tool that enables facility-level quantification of the nursing staffing gap using an internationally validated framework.")
body("The problem the project addresses is both real and consequential. Nepalese hospitals face nursing workforce challenges that the 2021 Nepal Health Facility Survey documented quantitatively and that published burnout studies have linked directly to patient safety outcomes. WISN Core provides a technically sound, legally grounded, and operationally feasible tool to begin addressing this problem systematically.")
body("The project's significance extends across several dimensions:")
blt("Hospital management — the tool enables administrators to identify departments under excessive workload pressure, allocate existing staff more equitably across departments, and build quantitative evidence for staffing requests to hospital boards or MoHP planning bodies.")
blt("Nursing staff — evidence-based identification of understaffed departments provides objective justification for staffing improvements, countering anecdotal or administrative resistance.")
blt("Nepal's health system — the project contributes to strengthening workforce-planning capacity by making an internationally validated methodology accessible and actionable at the facility level, aligned with the MoHP's stated commitment to WISN-based HRH planning.")
blt("Health informatics education — the project demonstrates the complete lifecycle of a health informatics application, from requirements analysis through empirically validated implementation to testing, within a single academic project.")
body("At the same time, the project acknowledges its limitations. It is a prototype requiring additional phases before full-scale deployment: multi-facility support, role-based access control, live EHR integration, and a formal clinical validation study using real facility data. These are not deficiencies in the current scope but natural extensions for future development.")
body("The working prototype, its documentation, and its validated calculations form a concrete foundation for future development and, with appropriate extension and validation, could become a meaningful tool for health workforce planning in Nepal's provincial and district health systems.")

h1('6.2 Future Work')
body("The following extensions are identified for future development iterations:")
blt("Multi-facility federation. Introduce a hospital-level entity above the department structure, enabling cross-facility comparison and provincial aggregation of WISN results.")
blt("Live health-record integration. Connect to an existing electronic health record system (such as OpenMRS or a FHIR-compliant API) to pull workload volumes automatically, eliminating manual data entry.")
blt("Role-based access control. Extend the authentication system to support multiple user roles (Facility Admin, Department Head, Read-Only Viewer) with appropriate permissions for each.")
blt("Shift scheduling module. Integrate the calculated WISN staffing requirement with a nurse roster planner that enforces Labour Act constraints on shift lengths and overtime.")
blt("Predictive burnout analytics. Implement a time-series model that tracks WISN ratio trends over rolling quarters, generating early-warning alerts when a department's ratio approaches the critical threshold.")
blt("Provincial deployment. Package the application for deployment on Nepal's Provincial Health Directorates' infrastructure, with multi-tenancy support for district hospital networks.")
blt("Mobile-responsive refinement. Further optimise the interface for tablet use in ward-level environments where desktop access may be limited.")

# ═══════════════════════════════════════════════════════════════════════════
# REFERENCES
# ═══════════════════════════════════════════════════════════════════════════
ch('References')

refs = [
    ("[1]", "Ministry of Health and Population (MoHP), Nepal / New ERA / ICF / DHS Program. (2022). "
            "Nepal Health Facility Survey 2021 — Final Report. ICF International, Rockville, Maryland, USA. "
            "Available: https://www.dhsprogram.com/pubs/pdf/SPA35/SPA35.pdf"),
    ("[2]", "World Health Organization. (2023). Workload Indicators of Staffing Need (WISN) — User's Manual, 2nd ed. "
            "WHO, Geneva. Available: https://iris.who.int/bitstream/handle/10665/373473/9789240070066-eng.pdf"),
    ("[3]", "Government of Nepal. (2017). The Labour Act, 2074 (2017). Nepal Law Commission. "
            "Available: https://antislaverylaw.ac.uk/wp-content/uploads/2019/08/The-Labour-Act-2017.pdf"),
    ("[4]", "Government of Nepal. (1997). Nepal Health Service Act, 2053 (1997). Nepal Law Commission. "
            "Available: https://www.siddhasthalihospital.org/wp-content/uploads/2022/05/1576743616nepal-health-service-act-2053-1997.pdf"),
    ("[5]", "Government of Nepal. (1999). Nepal Health Service Rules, 2055 (1999). "
            "Available: https://shisiradhikari.com.np/library/246/322"),
    ("[6]", "Ministry of Health and Population (MoHP), Nepal / NHSSP. "
            "Minimum Service Standards (MSS) for Primary Hospitals. Available: https://nhssp.org.np/"),
    ("[7]", "Tiwari, S., Tiwari, J. S., Jha, J. B., Regmi, S., et al. (2024). "
            "Admission Rate of Patients Visiting Emergency Department in a Tertiary Care Center in Kathmandu: "
            "A Descriptive Cross-sectional Study. JNMA J Nepal Med Assoc, 62(277), 587–591. "
            "Available: https://pmc.ncbi.nlm.nih.gov/articles/PMC11665762/"),
    ("[8]", "Kanak, M. P., Pant, S., Fradelos, E. C., Campbell, E., & Robinson, J. (2025). "
            "Burnout and sleep problems among nurses working in a tertiary hospital in Kathmandu, Nepal. "
            "PLOS Global Public Health, 5(7), e0003879. "
            "Available: https://pmc.ncbi.nlm.nih.gov/articles/PMC12250318/"),
    ("[9]", "Shah, S. K., Sinha, R., Neupane, P., & Kandel, G. (2024). "
            "Burnout among Nurses and Doctors Working at a Tertiary Care Government Hospital: "
            "A Descriptive Cross-sectional Study. JNMA J Nepal Med Assoc, 62(273), 293–296. "
            "Available: https://pmc.ncbi.nlm.nih.gov/articles/PMC11261546/"),
    ("[10]", "Journal of General Practice and Emergency Medicine of Nepal (JGPEMN). (2022). "
             "Audit of observation ward supporting emergency room at tertiary care hospital: Kathmandu, Nepal. "
             "Available: https://www.jgpemn.org.np/"),
    ("[11]", "Shagama, M., et al. (2016). Applying the Workload Indicators of Staffing Need (WISN) method in Namibia: "
             "challenges and implications for HRH policy. BMC Health Services Research. "
             "Available: https://pmc.ncbi.nlm.nih.gov/?term=WISN+Namibia+challenges+implications+human+resources"),
    ("[12]", "Tziaferi, S., et al. (2019). The implementation process of the Workload Indicators Staffing Need (WISN) "
             "method by WHO in determining midwifery staff requirements in Greek Hospitals. "
             "European Journal of Midwifery. Available: https://www.europeanjournalofmidwifery.eu/"),
    ("[13]", "Laravel LLC / Otwell, T. (2023). Laravel 10.x Documentation. "
             "Available: https://laravel.com/docs/10.x"),
    ("[14]", "van de Heuvel, B. (2023). barryvdh/laravel-dompdf — GitHub Repository. "
             "Available: https://github.com/barryvdh/laravel-dompdf"),
    ("[15]", "Chart.js Contributors. (2023). Chart.js 4.4 Documentation. "
             "Available: https://www.chartjs.org/docs/4.4.1/"),
    ("[16]", "Tailwind Labs. (2023). Tailwind CSS v3 Documentation. "
             "Available: https://v3.tailwindcss.com/docs"),
    ("[17]", "Ministry of Health and Population, Nepal / DHS Program. (2022). "
             "Nepal 2021 Health Facility Survey: Key Findings [SR273]. "
             "Available: https://dhsprogram.com/pubs/pdf/SR273/SR273.pdf"),
]

for num, text in refs:
    p = doc.add_paragraph(style='Normal')
    # Number in bold, text normal
    rn = p.add_run(num + '  '); _font(rn, bold=True)
    rt = p.add_run(text);        _font(rt)
    p.paragraph_format.left_indent       = Inches(0.5)
    p.paragraph_format.first_line_indent = Inches(-0.5)   # hanging indent
    p.paragraph_format.space_after       = Pt(8)

# ═══════════════════════════════════════════════════════════════════════════
# SECTION & PAGE-NUMBER CONFIGURATION
# ═══════════════════════════════════════════════════════════════════════════
# After building, doc.sections[0]=cover, [1]=front-matter, [2]=main content

# Section 1 — cover page: suppress footer on the single cover page
sec1 = doc.sections[0]
sec1.different_first_page_header_footer = True   # first-page footer left empty → no page number

# Section 2 — front matter: Roman numeral footer (I, II, III …)
sec2 = doc.sections[1]
sec2.different_first_page_header_footer = False
_add_page_footer(sec2, ' PAGE \\* ROMAN ')

# Section 3 — main content: Arabic numeral footer (1, 2, 3 …)
# Inject pgNumType decimal/start=1 into the body-level sectPr
body_spr = doc.element.body.sectPr
existing_pg = body_spr.find(qn('w:pgNumType'))
if existing_pg is not None:
    body_spr.remove(existing_pg)
pgNum3 = OxmlElement('w:pgNumType')
pgNum3.set(qn('w:fmt'),   'decimal')
pgNum3.set(qn('w:start'), '1')
body_spr.append(pgNum3)

sec3 = doc.sections[2]
sec3.different_first_page_header_footer = False
_add_page_footer(sec3)

# ── Save ──────────────────────────────────────────────────────────────────
doc.save(DEST)
print(f"✓ Saved: {DEST}")
