# SNSU iReserve - System Flow Diagrams

## 🔄 Complete Reservation Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│                     STUDENT RESERVATION FLOW                         │
└─────────────────────────────────────────────────────────────────────┘

Step 1: Browse & Add to Cart
┌──────────┐      ┌──────────────┐      ┌──────────────┐
│ Student  │─────>│   Equipment  │─────>│  Add Items   │
│  Login   │      │   Catalog    │      │  to Cart     │
└──────────┘      └──────────────┘      └──────────────┘
                                               │
                                               v
Step 2: Checkout & Submit
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Review     │─────>│  Fill Form   │─────>│   Submit     │
│    Cart      │      │ Date/Time/   │      │ Reservation  │
│              │      │   Purpose    │      │              │
└──────────────┘      └──────────────┘      └──────────────┘
                                                    │
                                                    v
                           ┌────────────────────────────────┐
                           │  Status: PENDING               │
                           │  QR Code: Generated            │
                           │  Notification: Sent to Faculty │
                           └────────────────────────────────┘
                                                    │
                                                    v
Step 3: Faculty Review
┌──────────────┐                          ┌──────────────┐
│   Faculty    │────> Review Request ────>│   Approve    │
│  Receives    │                          │      or      │
│Notification  │                          │   Reject     │
└──────────────┘                          └──────────────┘
                                                    │
                         ┌──────────────────────────┴──────────────┐
                         │                                          │
                         v                                          v
                  ┌─────────────┐                           ┌─────────────┐
                  │  APPROVED   │                           │  CANCELLED  │
                  │  QR Code    │                           │  With Reason│
                  │  Available  │                           │             │
                  └─────────────┘                           └─────────────┘
                         │                                          │
                         v                                          v
Step 4: Equipment Pickup                                    (End - Student
┌──────────────┐      ┌──────────────┐                      Notified)
│   Student    │─────>│    Staff     │
│  Shows QR    │      │  Scans QR    │
│    Code      │      │              │
└──────────────┘      └──────────────┘
                              │
                              v
                     ┌─────────────────┐
                     │  Status: ISSUED │
                     │  Equipment Given│
                     └─────────────────┘
                              │
                              v
Step 5: Equipment Use
┌─────────────────────────────┐
│   Student Uses Equipment    │
└─────────────────────────────┘
                │
                v
Step 6: Return Process
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Student    │─────>│    Staff     │─────>│   Status:    │
│  Requests    │      │  Verifies &  │      │  COMPLETED   │
│   Return     │      │  Processes   │      │              │
└──────────────┘      └──────────────┘      └──────────────┘
```

---

## 👥 User Role Access Matrix

```
┌──────────────────────────────────────────────────────────────────┐
│                        SYSTEM ACCESS LEVELS                       │
└──────────────────────────────────────────────────────────────────┘

Feature/Function          │ Student │ Faculty │ Staff │ Admin │
─────────────────────────────────────────────────────────────────
Browse Equipment          │    ✓    │    ✓    │   ✓   │   ✓   │
Add to Cart              │    ✓    │    ✓    │   ✓   │   ✓   │
Create Reservation       │    ✓    │    ✓    │   ✓   │   ✓   │
View Own Reservations    │    ✓    │    ✓    │   ✓   │   ✓   │
Cancel Own Reservation   │    ✓    │    ✓    │   ✓   │   ✓   │
Download QR Code         │    ✓    │    ✓    │   ✓   │   ✓   │
Request Return           │    ✓    │    ✓    │   ✓   │   ✓   │
─────────────────────────────────────────────────────────────────
Approve Reservations     │    ✗    │    ✓    │   ✓   │   ✓   │
Reject Reservations      │    ✗    │    ✓    │   ✓   │   ✓   │
Scan QR Codes           │    ✗    │    ✓    │   ✓   │   ✓   │
Issue Equipment          │    ✗    │    ✓    │   ✓   │   ✓   │
Process Returns          │    ✗    │    ✓    │   ✓   │   ✓   │
View Dept. Reservations  │    ✗    │    ✓    │   ✓   │   ✓   │
─────────────────────────────────────────────────────────────────
View All Reservations    │    ✗    │    ✗    │   ✗   │   ✓   │
Manage Equipment         │    ✗    │    ✗    │   ✗   │   ✓   │
Manage Users             │    ✗    │    ✗    │   ✗   │   ✓   │
Manage Departments       │    ✗    │    ✗    │   ✗   │   ✓   │
System Settings          │    ✗    │    ✗    │   ✗   │   ✓   │
View Analytics           │    ✗    │    ✗    │   ✗   │   ✓   │
Bulk Operations          │    ✗    │    ✗    │   ✗   │   ✓   │
─────────────────────────────────────────────────────────────────
```

---

## 🗄️ Database Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE RELATIONSHIPS                      │
└─────────────────────────────────────────────────────────────────┘

                    ┌──────────────┐
                    │ departments  │
                    │──────────────│
                    │ id           │
                    │ name         │
                    │ code         │
                    │ is_active    │
                    └──────────────┘
                           │
                           │ 1:N
                           │
                    ┌──────▼───────┐
                    │    users     │
                    │──────────────│
                    │ id           │
                    │ name         │
                    │ email        │
                    │ role         │
    ┌───────────────│ department_id│◄─────────────┐
    │               └──────────────┘              │
    │ 1:N                  │                      │
    │                      │ 1:N                  │ N:1 (issued_by)
    │                      │                      │
    │               ┌──────▼───────┐              │
    │               │ reservations │              │
    │               │──────────────│              │
    │               │ id           │──────────────┘
    │               │ user_id      │──────────────┐
    │               │ res_code     │              │ N:1 (returned_by)
    │               │ res_date     │              │
    │               │ status       │              │
    │               │ qr_code_data │              │
    │               │ approved_by  │──────────────┘
    │               │ issued_by    │
    │               │ returned_by  │
    │               └──────────────┘
    │                      │
    │                      │ 1:N
    │                      │
    │               ┌──────▼─────────┐
    │               │ reservation_   │
    │               │     items      │
    │               │────────────────│
    │               │ id             │
    │               │ reservation_id │
    │               │ equipment_id   │───┐
    │               │ quantity       │   │
    │               │ status         │   │
    │               └────────────────┘   │
    │                                    │ N:1
    │                                    │
    │                             ┌──────▼──────┐
    │                             │  equipment  │
    │                             │─────────────│
    │                             │ id          │
    │                             │ name        │
    │                             │ category    │
    │                             │ status      │
    │                             │ total_qty   │
    │                             │ avail_qty   │
    │                             └─────────────┘
    │
    │
    └───────────────────────────> ┌──────────────────┐
                                  │  notifications   │
                                  │──────────────────│
                                  │ id               │
                                  │ notifiable_id    │
                                  │ notifiable_type  │
                                  │ type             │
                                  │ data             │
                                  │ is_read          │
                                  └──────────────────┘
```

---

## 🔄 Equipment Status State Machine

```
┌─────────────────────────────────────────────────────────────────┐
│                    EQUIPMENT STATUS FLOW                         │
└─────────────────────────────────────────────────────────────────┘

                         ┌─────────────┐
                    ────>│  AVAILABLE  │<────┐
                    │    └─────────────┘     │
                    │           │            │
                    │           │ Reserved   │
                    │           │            │
                    │    ┌──────▼──────┐     │
                    │    │  RESERVED   │     │
          Repaired/ │    │  (pending/  │     │ Returned/
          Replaced  │    │  approved)  │     │ Cancelled
                    │    └──────┬──────┘     │
                    │           │            │
                    │           │ Issued     │
                    │           │            │
                    │    ┌──────▼──────┐     │
                    │    │   ISSUED    │─────┘
                    │    │ (in use by  │
                    │    │   student)  │
                    │    └──────┬──────┘
                    │           │
                    │           │ Damaged/
                    │           │ Broken
                    │           │
                    │    ┌──────▼──────────┐
                    └────│     UNDER       │
                         │  MAINTENANCE    │
                         └─────────────────┘
                                │
                                │ Disposed
                                │
                         ┌──────▼──────┐
                         │ UNAVAILABLE │
                         │  (removed)  │
                         └─────────────┘
```

---

## 📊 Reservation Status State Machine

```
┌─────────────────────────────────────────────────────────────────┐
│                   RESERVATION STATUS FLOW                        │
└─────────────────────────────────────────────────────────────────┘

         Student Submits
              Request
                 │
                 v
          ┌─────────────┐
          │   PENDING   │
          └─────────────┘
                 │
      ┌──────────┴──────────┐
      │                     │
      v                     v
┌───────────┐         ┌───────────┐
│ CANCELLED │         │ APPROVED  │
│ (rejected)│         └───────────┘
└───────────┘               │
                            v
                  Student Presents QR
                            │
                            v
                     ┌─────────────┐
                     │   ISSUED    │
                     └─────────────┘
                            │
                            v
                  Student Requests Return
                            │
                            v
                  ┌──────────────────┐
                  │ RETURN_REQUESTED │
                  └──────────────────┘
                            │
                            v
                   Staff Processes Return
                            │
                            v
                     ┌─────────────┐
                     │  COMPLETED  │
                     └─────────────┘
```

---

## 🔔 Notification Trigger Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                     NOTIFICATION SYSTEM                          │
└─────────────────────────────────────────────────────────────────┘

Event Trigger                  Notification Sent To          Type
─────────────────────────────────────────────────────────────────

Student Creates                 Faculty/Admin           [New Request]
Reservation            ────────> (of student's
                                  department)

Faculty Approves                Student                [Request Approved]
Reservation            ────────> (reservation owner)   + QR Available

Faculty Rejects                 Student                [Request Rejected]
Reservation            ────────> (reservation owner)   + Reason

Staff Issues                    Student                [Equipment Issued]
Equipment              ────────> (reservation owner)

Student Requests                Faculty/Admin          [Return Request]
Return                 ────────> (handling dept)

Staff Processes                 Student                [Return Completed]
Return                 ────────> (reservation owner)

Equipment Added                 All Admins             [System Update]
to Cart                ────────> (system-wide)

Admin Adds New                  All Users              [New Equipment]
Equipment              ────────> (system-wide)         Available

System Maintenance              All Admins             [System Alert]
Alert                  ────────> (system-wide)
```

---

## 🔐 Security Layer Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    SECURITY ARCHITECTURE                         │
└─────────────────────────────────────────────────────────────────┘

                    ┌───────────────┐
                    │  User Request │
                    └───────┬───────┘
                            │
                            v
                    ┌───────────────┐
                    │  HTTPS/SSL    │ ← Encrypted Connection
                    └───────┬───────┘
                            │
                            v
                    ┌───────────────┐
                    │ CSRF Token    │ ← CSRF Protection
                    └───────┬───────┘
                            │
                            v
                    ┌───────────────┐
                    │ Authentication│ ← Session/Token Check
                    └───────┬───────┘
                            │
                    ┌───────┴────────┐
                    │ Verified User? │
                    └───────┬────────┘
                       YES  │  NO
                    ┌───────┴──────┐
                    │              │
                    v              v
            ┌──────────────┐  ┌─────────┐
            │ Authorization│  │  Login  │
            │ (Role Check) │  │  Page   │
            └──────┬───────┘  └─────────┘
                   │
            ┌──────┴───────┐
            │ Has Access?  │
            └──────┬───────┘
              YES  │  NO
            ┌──────┴──────┐
            │             │
            v             v
    ┌──────────────┐  ┌────────┐
    │Rate Limiting │  │  403   │
    └──────┬───────┘  │Forbidden│
           │          └────────┘
           v
    ┌──────────────┐
    │  Input       │
    │  Validation  │
    └──────┬───────┘
           │
           v
    ┌──────────────┐
    │  Sanitization│
    └──────┬───────┘
           │
           v
    ┌──────────────┐
    │  Controller  │
    │   Action     │
    └──────┬───────┘
           │
           v
    ┌──────────────┐
    │  Database    │ ← Parameterized Queries
    │   (ORM)      │   (SQL Injection Prevention)
    └──────┬───────┘
           │
           v
    ┌──────────────┐
    │   Response   │
    └──────┬───────┘
           │
           v
    ┌──────────────┐
    │   Logging    │ ← Audit Trail
    └──────────────┘
```

---

## 📱 System Integration Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                   SYSTEM COMPONENT INTEGRATION                   │
└─────────────────────────────────────────────────────────────────┘

                    ┌──────────────────┐
                    │   Web Browser    │
                    │  (Vue.js SPA)    │
                    └────────┬─────────┘
                             │
                    HTTPS    │    Inertia.js
                             │
                    ┌────────▼─────────┐
                    │  Laravel Backend │
                    │   (PHP 8.2+)     │
                    └────────┬─────────┘
                             │
            ┌────────────────┼────────────────┐
            │                │                │
            v                v                v
    ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
    │   MySQL      │ │   File       │ │   Pusher     │
    │   Database   │ │   Storage    │ │  (Real-time) │
    └──────────────┘ └──────────────┘ └──────────────┘
            │                │                │
            │                │                │
            v                v                v
       [User Data]      [QR Codes]      [Notifications]
       [Equipment]      [Images]        [Live Updates]
       [Reservations]   [Uploads]

              ┌──────────────────────┐
              │   Queue Worker       │
              │  (Background Jobs)   │
              └──────────────────────┘
                       │
                       v
              [Email Notifications]
              [Report Generation]
              [Data Processing]
```

---

## 🎯 Quick Reference: Key URLs

```
┌─────────────────────────────────────────────────────────────────┐
│                        SYSTEM ROUTES                             │
└─────────────────────────────────────────────────────────────────┘

PUBLIC ROUTES:
/                              → Landing Page
/login                         → Login Page
/register                      → Registration Page
/forgot-password               → Password Reset

STUDENT ROUTES:
/student-dashboard             → Student Dashboard
/student/equipment             → Equipment Catalog
/student/cart/checkout         → Cart Checkout
/student/issued-equipment      → Issued Equipment
/student/requested-equipment   → Requested Equipment
/student/reservation/{id}/qr   → View QR Code

FACULTY ROUTES:
/faculty-dashboard             → Faculty Dashboard
/faculty/reservations          → Manage Reservations
/faculty/qr-scanner            → QR Scanner
/faculty/issue-equipment       → Issue Equipment
/faculty/issued-equipment      → Issued Equipment List
/faculty/requested-equipment   → Pending Requests

ADMIN ROUTES:
/admin-dashboard               → Admin Dashboard
/admin/users                   → User Management
/admin/students                → Student Management
/admin/equipment               → Equipment Management
/admin/departments             → Department Management
/admin/reservations            → All Reservations
/admin/qr-scanner              → Admin QR Scanner
/admin/notifications           → Notifications Center

SHARED ROUTES:
/profile                       → User Profile
/reservations                  → User's Reservations
/admin/notifications           → Notification Center
```

---

## 📈 Performance Optimization Points

```
┌─────────────────────────────────────────────────────────────────┐
│                   PERFORMANCE OPTIMIZATIONS                      │
└─────────────────────────────────────────────────────────────────┘

DATABASE LAYER:
├─ Indexed Columns
│  ├─ users: role, department_id, email
│  ├─ reservations: status, user_id, reservation_date
│  ├─ equipment: status, category, serial_number
│  └─ notifications: is_read, type, created_at
│
├─ Eager Loading (N+1 Prevention)
│  ├─ Load related models in single query
│  └─ Example: Reservation::with(['items.equipment', 'user'])
│
└─ Query Optimization
   ├─ Select only needed columns
   ├─ Pagination for large datasets
   └─ Composite indexes for common queries

CACHING LAYER:
├─ User Reservations (5 min TTL)
├─ Dashboard Statistics (5 min TTL)
├─ Equipment Availability (5 min TTL)
└─ Department List (10 min TTL)

FRONTEND LAYER:
├─ Code Splitting (Lazy Loading)
├─ Asset Minification (Vite)
├─ Image Optimization (Compression)
└─ Browser Caching (Static Assets)

APPLICATION LAYER:
├─ Queue Jobs (Background Processing)
├─ Rate Limiting (Abuse Prevention)
├─ Session Management (Efficient Storage)
└─ Soft Deletes (Recovery without bloat)
```

---

**Document Version:** 1.0
**Last Updated:** October 15, 2025
**Companion to:** CLIENT_PROJECT_OVERVIEW.md
