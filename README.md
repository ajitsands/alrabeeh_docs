# SaNDS Lab & Al Rabeeh Dental Center EMRS Document Portal & Tracking Engine

## Overview
This project implements the enterprise email authentication, device persistence, and granular access tracking system adapted from the reference architecture in `PopularMileStones` directly into the `Rabeesh _v1` project.

All access to HTML specifications and PDF downloads requires corporate email authorization. Once authorized, stakeholders can read interactive HTML documents online and download official PDFs. Every interaction (HTML read vs. PDF download) is logged in real-time in the local MySQL database **`alrabeeh_track_db`**.

---

## Architecture & Implementation Summary

### 1. Database Configuration (`config.php`)
- **Database Engine**: MySQL 8.0+ (InnoDB)
- **Host**: `127.0.0.1` / Port `3306`
- **Database Name**: `alrabeeh_track_db`
- **Username**: `root`
- **Password**: `S@nds1@b`
- **Salt**: 256-bit cryptographic token key for device fingerprinting
- **Fallback**: High-concurrency SQLite WAL engine (`.auth_portal.db`)

### 2. Database Schema (`schema.sql`)
1. **`authorized_users`**: Whitelisted corporate stakeholder directory (Email, Full Name, Organization, Role, Active Status).
2. **`authenticated_devices`**: Remembered devices linked via SHA-256 fingerprint tokens stored in a 5-year persistent cookie (`sands_auth_device`).
3. **`document_meta`**: Lifecycle tracking, lock states, and document status (`IN_REVIEW`, `FINALIZED_AND_LOCKED`).
4. **`document_access_logs`**: Core analytics table tracking:
   - Stakeholder email
   - Document ID & Title
   - **Action Type**: `VIEW_HTML` (Read) vs. `DOWNLOAD_PDF` (Download)
   - IP address & Geolocation lookup
   - User Agent & parsed device name (OS & Browser)
   - Accessed timestamp
5. **`otp_logs`**: Audit trail of 6-digit verification codes generated and dispatched.
6. **`ip_cache`**: High-speed IP-to-location cache table to eliminate API lookup latency.
7. **`document_signatures`**: Digital tripartite sign-off records and revision requests.

### 3. Document Registry & Routes (`index.php`)
- **`SL-ARDC-EMRS-2026-V1`**: Master Proposal & Technical Architecture (`Al_Rabeeh_EMRS_Detailed_Proposal.html` / `Al_Rabeeh_EMRS_Detailed_Proposal.pdf`)
- **`ARDC-RESOURCE-PRICING-2026`**: Resource Cost & 12-Milestone Pricing Matrix (`Al_Rabeeh_Internal_Resource_Pricing.html`)
- **`ARDC-EMRS-FLOWCHART-2026`**: 11-Module Architectural Flowchart (`emrs_flow_chart.html`)
- **`ARDC-NAV-SEQUENCE-2026`**: Unified Navigation Sequence (`navigation_sequence_flow_chart.html`)
- **`ARDC-INTERNAL-REF-2026`**: Master Pricing & Governance Reference (`Internal_Resource_Pricing_Reference.html`) — **Strictly Restricted: Accessible ONLY by `ajit@sandslab.com`** (hidden from all other stakeholder dashboards).
- **`ARDC-SOFTWARE-BLUEPRINT`**: Original 35-Page Dental Reception Blueprint (`EMRS-Software_Blueprint (1).pdf`)

### 4. Zero-Friction Authentication Flow
1. **Unauthenticated Visit**: When visiting `index.php` or clicking any document link, the system intercepts the request and displays the Al Rabeeh & SaNDS Lab branded authentication console.
2. **Email Verification**: User enters their corporate email address. The system checks `authorized_users` where `is_active = 1`.
3. **6-Digit OTP Delivery**: A random 6-digit code is generated and dispatched via corporate HTML email (with 15-minute validity).
4. **Device Authorization**: Upon entering the valid code:
   - A 5-year persistent cookie `sands_auth_device` is installed with a unique device fingerprint.
   - The device is stored in `authenticated_devices`.
   - `LOGIN_VERIFIED` event is logged in `document_access_logs`.
   - The stakeholder is redirected immediately to their intended document or the portal hub.

### 5. Access & Download Tracking
- **Reading HTML**: When a stakeholder views an HTML document, `index.php` logs an audit entry with `action_type = 'VIEW_HTML'`.
- **Downloading PDF**: When clicking "Download PDF", `index.php` logs an audit entry with `action_type = 'DOWNLOAD_PDF'` and streams the PDF file.
- **Top Utility Navigation Bar**: Embedded in every HTML document, providing 1-click PDF download, back-to-hub link, authenticated user identity pill, and Super Admin analytics shortcut.

### 6. Apache Request Interception (`.htaccess`)
- Rewrites all direct requests to `*.html` and `*.pdf` files through `index.php` so unauthenticated visitors cannot bypass authentication by typing direct file URLs.
- Rewrites clean document codes (e.g. `/SL-ARDC-EMRS-2026-V1`) to `index.php?doc=SL-ARDC-EMRS-2026-V1`.

### 7. Super Admin & Analytics Console (`index.php?view=admin`)
Accessible by **`ajit@sandslab.com`** and **`projects@sandslab.com`**:
- **KPI Metrics Bar**: Total HTML reads, Total PDF downloads, Registered stakeholders, Active devices, MySQL status.
- **Document Access Breakdown**: Granular breakdown of HTML reads, PDF downloads, total interactions, unique users, and last access timestamp per document.
- **Stakeholder Engagement Matrix**: Table displaying each user's total HTML reads, total PDF downloads, last active timestamp, last device, and location, with 1-click buttons to revoke devices or pause access.
- **Real-Time Audit Log**: Searchable, filterable DataTables log showing the latest 500 access events.
- **Stakeholder Management**: Add new authorized stakeholders or edit existing users.

---

## Seed Stakeholders
| Email Address | Full Name | Organization | Role |
| :--- | :--- | :--- | :--- |
| `ajit@sandslab.com` | Ajit Kumar KV | SaNDS Lab Middle East W.L.L | Super Admin |
| `projects@sandslab.com` | SaNDS Lab Administration | SaNDS Lab Middle East W.L.L | Admin |
| `info@sandslab.com` | SaNDS Lab Middle East W.L.L | SaNDS Lab Middle East W.L.L | Admin |
| `director@alrabeeh.bh` | Managing Director | Al Rabeeh Dental Center | Client Director |
| `management@alrabeeh.bh` | Executive Management Team | Al Rabeeh Dental Center | Client Executive |
| `it@alrabeeh.bh` | IT & Systems Team | Al Rabeeh Dental Center | Client IT |
| `clinical@alrabeeh.bh` | Clinical & Medical Director | Al Rabeeh Dental Center | Clinical Lead |

---
&copy; 2026 SaNDS Lab Middle East W.L.L. All Rights Reserved.

