# Prowave ERP — HR & Staff Management Module

## Plan (approved, Option A: reuse User model as Employee)

### Phase 1 — Database & Enums ✅
- [x] Migration: create_designations_table
- [x] Migration: create_shifts_table
- [x] Migration: add_employee_fields_to_users (father_name, whatsapp, salary, employment_type, employee_status, shift_id)
- [x] Migration: create_attendance_table
- [x] Migration: create_leave_types_table
- [x] Migration: create_leave_requests_table
- [x] Migration: create_employee_leave_balances_table
- [x] Migration: create_daily_tasks_table
- [x] Migration: create_daily_work_updates_table
- [x] Enums: EmployeeStatus, EmploymentType, AttendanceStatus, LeaveStatus, TaskStatus, Priority, DailyUpdateStatus

### Phase 2 — Models, Policies, Repositories, Services ✅
- [x] Models: Designation, Shift, Attendance, LeaveType, LeaveRequest, EmployeeLeaveBalance, DailyTask, DailyWorkUpdate
- [x] User model relations + casts
- [x] Policies: Employee, Attendance, Leave, Task, DailyUpdate
- [x] Repositories: Employee, Attendance, Leave, Task, DailyUpdate
- [x] Services: Attendance (shift calc), Leave (balance), Task, DailyUpdate

### Phase 3 — Controllers & Routes
- [ ] Controllers under App\Http\Controllers\Hr\
- [ ] Routes hr.* group + permission middleware

### Phase 4 — Form Requests
- [ ] Form Requests for all store/update ops

### Phase 5 — Views (premium, responsive)
- [ ] Staff index/create/edit/show (profile tabs)
- [ ] Departments/Designations CRUD
- [ ] Shifts CRUD
- [ ] Attendance dashboard + index + check-in/out
- [ ] Leave dashboard + index + create + review
- [ ] Daily tasks board/list/form
- [ ] Daily updates submit/review
- [ ] HR Dashboard
- [ ] Reports (Excel/PDF)

### Phase 6 — Permissions
- [ ] Seeder: add HR permission groups + role assignment

### Phase 7 — Sidebar & Assets
- [ ] Sidebar HR section (permission-guarded)
- [ ] CSS HR styles + mobile responsiveness

### Phase 8 — Verify
- [ ] migrate, db:seed, npm run build
- [ ] Regression tests + mobile check
