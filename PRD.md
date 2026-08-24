# PRD: Internal hiring system (interview to offer)

**Status:** Draft for build
**Audience:** Engineer building this from scratch
**Stack:** Laravel 13 · Inertia 3 · Vue 3 (TypeScript) · Tailwind · MySQL

---

## Executive summary

We hire manually today. Interview slots are negotiated over chat, feedback lives in
people's heads, and nobody can answer "who is in flight and what is blocking them?"
without asking three people. This PRD describes an internal applicant tracking system
for one organisation that owns the hiring pipeline from the first post-screening
interview through to an accepted offer.

It is a staff-facing tool, not a product for candidates. Candidates never log in.
The users are four kinds of employee — admins, recruiters, hiring managers, and
interviewers — and the system's job is to make interview coordination boring,
evaluation comparable, and every past decision reconstructible.

---

## Problem statement and goals

### Problem

Three problems, in the order they hurt:

1. **Coordination chaos.** Back-and-forth on interview slots, interviewers
   double-booked, nobody sure whose turn it is to act.
2. **Inconsistent evaluation.** Every interviewer asks different things. Feedback
   lands in chat threads. Decisions are gut-feel and hard to defend three months later.
3. **No pipeline visibility.** Candidates go cold and stages stall silently, because
   nothing surfaces a stalled candidate until someone thinks to look.

### Goals

- One screen answers "where does every candidate stand?"
- Every interview produces structured, comparable feedback attributed to a named person.
- A hiring decision made six months ago can be reconstructed from an audit trail.
- Stalled candidates surface themselves rather than waiting to be noticed.
- Compensation is never visible to interviewers — enforced, not merely hidden.

---

## Target users

| User | Needs |
|---|---|
| **Admin / leadership** | Manage staff accounts and the reusable templates the process runs on. Full visibility. Final approver on offers. |
| **Recruiter / coordinator** | Add candidates, move them through stages, schedule interviews, chase feedback, draft offers. Needs the whole pipeline at a glance. |
| **Hiring manager** | Owns requisitions. Needs candidates and feedback for **their own** openings, and makes advance/reject calls on them. |
| **Interviewer** | Needs only their assigned interviews, enough candidate context to run them, and a scorecard to fill in. Nothing else. |

Roles are **additive**. A hiring manager who also runs technical interviews holds both
roles and must need no special-casing anywhere in the system.

---

## Scope

### In scope

- Staff accounts, authentication, and the four roles above.
- Job openings (internal requisitions).
- Reusable pipeline templates and scorecard templates.
- Candidates, and applications of a candidate to an opening.
- Per-application stage tracking with decisions and reasons.
- Interview records with assigned panels, rescheduling, and cancellation.
- Scorecard submission and aggregation.
- Per-opening board, cross-opening pipeline table, and a role-aware dashboard.
- In-app notifications and an append-only activity log.
- **Phase 2:** Google Calendar event creation, transactional email, a candidate-facing
  status page, a staleness digest.
- **Phase 3:** Offers with an approval chain, generated offer letters, and reporting.

### Out of scope

These are not "later" — they are not what this product is:

- Multi-tenancy, billing, or plans.
- A public job board, public application form, or resume parsing.
- Candidate sourcing.
- Candidate logins or a candidate portal.
- E-signature on offer letters.
- Slack integration, or pushing new hires into our HR system.

Candidates enter the system **already sourced and screened**, added by a recruiter.

### Assumptions

- Single organisation. No tenancy scoping anywhere, deliberately.
- Volume is tens of candidates in flight, not thousands. Do not build for scale we
  do not have.
- Phase 1 requires no external credentials and no network access. Calendar and email
  arrive in Phase 2 behind interfaces defined in Phase 1.
- Interview scheduling is coordinated by a human; the system records and notifies, it
  does not negotiate slots or offer candidate self-booking.

---

## User scenarios and key flows

### 1. Setup (admin, once)

Create scorecard templates → create pipeline templates, each an ordered list of stages
where an interview stage may point at a scorecard template → create job openings, each
assigned a pipeline template and a hiring manager.

### 2. Adding a candidate

The recruiter creates a candidate — or picks an existing person already in the system —
and opens an application against a job opening. The template's stages are **copied onto
the application**. The application becomes `active` and its first stage becomes current.
The recruiter may then skip, reorder, insert, or remove a stage **for this candidate
only**.

### 3. Running a stage

```
stage: pending → in_progress → passed | failed | skipped
```

The recruiter schedules one or more interviews on the stage: date/time, duration, mode
(onsite / video / phone), location or meeting link, and assigned interviewers.
Interviewers are notified in-app. Each interviewer submits their own scorecard —
per-criterion ratings, an overall recommendation, and notes. A recruiter or hiring
manager reviews the aggregate and records the stage decision with a reason.

`passed` makes the next stage current. `failed` rejects the application.

### 4. Application lifecycle

```
active → hired | rejected | withdrawn | on_hold
         on_hold ⇄ active
```

### 5. Daily use — the three surfaces

- **Per-opening board.** Columns are that opening's stages, cards are candidates. A card
  flags when it has sat in its stage past that stage's staleness threshold.
- **Cross-opening pipeline table.** Everyone currently in flight, filterable by opening,
  stage, owning recruiter, and staleness.
- **Dashboard.** An action queue of what is waiting on *the viewer*, not a metrics page.

### 6. Rejection

Available at any stage, with a reason plus optional notes. The candidate is archived as
part of the same action unless they are still in flight for another opening. Phase 2
attaches the candidate-facing email.

### 7. Offer (Phase 3)

Recruiter or hiring manager drafts an offer → `pending_approval` → hiring manager
approves → admin approves → letter PDF generated → `sent` → `accepted` / `declined` /
`expired`. Acceptance sets the application to `hired`. The opening closes automatically
once its hired count reaches its headcount.

---

## Functional requirements

### Accounts and permissions

1. Staff sign in with email and password, with two-factor authentication available.
2. A user holds one or more of: `admin`, `recruiter`, `hiring_manager`, `interviewer`.
3. Admins can create users, edit them, and **deactivate** them. Users are never deleted.
4. Deactivating a user who has upcoming interviews must warn, listing what needs
   reassignment — not block, and not silently orphan the interviews.
5. Every list and detail query is filtered by a shared visibility rule per entity. No
   screen may load "all records" and filter for display.

**Permission matrix**

| | Admin | Recruiter | Hiring manager | Interviewer |
|---|---|---|---|---|
| Users, templates | Manage | — | — | — |
| Job openings | All, manage | All, manage | Own: edit description/notes | — |
| Candidates & applications | All | All, create/edit | Own openings only | — |
| Move stage / advance / reject | Yes | Yes | Own openings | — |
| Schedule interviews, assign panel | Yes | Yes | Own openings | — |
| Scorecards | Read all | Read all | Read all on own openings | Own submissions only |
| Comp & offer details | Yes | Yes | Own openings | **Never** |
| Offer approval | Final approver | Draft only | First approver, own openings | — |
| Reporting | Yes | Yes | Own openings | — |

6. Hiring managers are scoped to their own openings. There is no cross-team browsing.
7. Interviewers see only their **own** scorecard submissions. Peer feedback is not part
   of an interviewer's view at all.
8. Compensation must never be present in a response sent to an interviewer. Build the
   interviewer's payload from a different object rather than hiding fields in the UI.
9. A user is never assignable as an interviewer on an application whose candidate email
   matches their own, and such applications are excluded from that user's own visibility.
   Enforce this server-side, not by hiding UI.

### Templates

10. Admins can create and edit **scorecard templates**: a name and an ordered set of
    criteria, each with a name and description.
11. All criteria are rated on a fixed **1–4** scale. Per-criterion custom scales are
    deliberately not supported — varying scales make submissions incomparable.
12. Admins can create and edit **pipeline templates**: a name, an active flag, and an
    ordered set of stages.
13. Each template stage has a name, position, kind (`interview` / `non_interview`), an
    optional scorecard template, a default duration in minutes, and a staleness
    threshold in days.

### Job openings

14. A job opening has a title, department (free text), hiring manager, pipeline
    template, headcount, description, notes, and status: `draft` / `open` / `on_hold` /
    `closed` / `filled`.
15. The filled count is **derived** from hired applications, never stored as a counter
    that could drift.
16. The opening's detail screen shows its pipeline board as the default view.

### Candidates and applications

17. A **candidate** is a person: name, email, phone, resume file, source, notes.
    A candidate can be **archived** and un-archived. Archiving is a visibility state, not a
    deletion: nothing is removed or scrubbed, the record stays reachable by direct link and
    behind an "include archived" filter, and it drops out of default lists and search. A
    candidate with an active application cannot be archived.
17a. A candidate is **archived automatically the moment they hold no live application**,
    where *live* means `active` or `on_hold`. One rule, not a list of statuses: rejection,
    withdrawal, and being hired all end a candidate's time in the working set, and the
    reason is already recorded on the application and in the activity log, so the archive
    does not need to encode it. A person rejected for one role while still in flight for
    another is not archived — they still hold a live application. `on_hold` is deliberately
    live: it is a pause, not an ending. Archiving is logged like any other state change, and
    a recruiter can un-archive at any time.
17b. Opening a new application for an archived candidate **un-archives them automatically**.
    Re-applying is the normal case, and making a recruiter un-archive by hand first would be
    a step that exists only to be forgotten.
18. An **application** is one candidate's run at one opening. The same person may hold
    several applications, concurrently or over time.
19. Creating an application copies the opening's pipeline template stages onto that
    application. Editing a template afterwards must never change an application already
    in flight.
20. Each application stage carries its own name, position, kind, scorecard template
    reference, staleness threshold, status, started/completed timestamps, and its
    decision, decider, and reason.
21. An application tracks the current stage directly so board queries stay cheap.
22. Recruiters can insert, remove, reorder, and skip stages on a single application
    without affecting the template or any other application.
23. Application status is `active` / `hired` / `rejected` / `withdrawn` / `on_hold`.
24. Resume files are stored privately and served only through an authorized, signed,
    expiring link. There must be no public URL for a candidate document.

### Interviews

25. An interview belongs to an application stage and has a scheduled time, duration,
    mode (`onsite` / `video` / `phone`), location or meeting URL, and status:
    `scheduled` / `completed` / `cancelled` / `no_show` / `rescheduled`.
26. An interview has one or more assigned participants, one optionally flagged as lead.
27. Assigned interviewers receive an in-app notification when scheduled.
28. Scheduling must **flag, not block**, when an assigned interviewer already has an
    overlapping interview in this system. We have no free/busy data; only our own
    records can be checked.
29. Rescheduling is never a destructive edit: close the old record as `rescheduled` and
    create a new one that references it, so "this candidate was moved three times"
    survives.
30. An interview can be cancelled, and can be closed out (marked completed / no-show)
    once it has happened.

### Scorecards

31. Each assigned interviewer submits **one** scorecard per interview: a rating and
    optional note per criterion, an overall recommendation (`strong_no` / `no` / `yes` /
    `strong_yes`), and summary notes.
32. A submission stores a snapshot of the criteria it was completed against, so renaming
    a criterion next quarter does not rewrite history.
33. A scorecard submitted **after** the stage has been decided is permitted and recorded,
    flagged as late. Blocking it just pushes the feedback back into chat.

### Decisions

34. A stage decision records the outcome, the decider, a reason, and the timestamp.
35. Decisions are **not** hard-blocked on outstanding scorecards. The UI must show who
    has not submitted; real hiring needs the escape hatch.
36. Passing the final stage leaves the application awaiting an offer.
37. Failing any stage rejects the application, with a reason.
38. Two people deciding the same stage concurrently must not overwrite each other. Lock
    and re-validate inside the transaction; tell the loser "this stage was already
    decided by X."
39. Every state change — stage, application, interview, opening, offer — writes an
    entry to an **append-only activity log** recording who, what, when, and why.

### Visibility surfaces

40. **Per-opening board:** one column per stage, one card per candidate, flagging any
    card past its stage's staleness threshold.
41. **Cross-opening pipeline table:** all in-flight applications, filterable by opening,
    stage, owning recruiter, and staleness.
42. **Dashboard:** an action queue of work waiting on the viewer, built from four
    signals, each gated by the same visibility rules as everything else:
    - *Feedback you owe* — interviews the viewer sat on and has not written up.
    - *Needs close-out* — interviews past their end time still marked `scheduled`.
    - *Ready to decide* — stages where every panel member has submitted and no decision
      has been recorded. A stage with no interviews is excluded: it is waiting on
      scheduling, not on a decision.
    - *Stale candidates* — applications past their current stage's threshold.
43. Dashboard rows link out to the screen that owns the work. The one exception is an
    inline "mark completed" on close-out rows.
44. A section with nothing in it does not render. When every section is empty, the page
    says so in one sentence — not four empty boxes.
45. An interviewer's dashboard must contain only the two interview sections, and no
    other candidate's data at all.
46. Nobody, admins included, sees their own application anywhere.
47. Interviewers get a dedicated "my interviews" screen: their upcoming interviews and
    the scorecards they owe.

### Phase 2 — outward-facing

48. Create a Google Calendar event when an interview is scheduled, with an optional
    Meet link, and update it on reschedule or cancellation.
49. Transactional email for interview invite, reschedule, and rejection, from templates
    an admin can edit, with documented placeholders.
50. A candidate-facing status page reached by a signed, expiring link — no login, no
    account. An expired link, and a superseded interview's link, respond **`410 Gone`**
    with a human explanation ("this link has expired", "this interview was rescheduled") —
    not a 404, and not a 200 pretending the link is still live.
51. A periodic digest of stale candidates to the owning recruiter.
52. Calendar and email sends run as background jobs with retries, and a send that failed
    must be **visible on the record in the UI**, not buried in a log.

### Phase 3 — offers and reporting

53. An offer belongs to an application: compensation, currency, start date, employment
    type, terms notes, expiry, status (`draft` / `pending_approval` / `approved` /
    `sent` / `accepted` / `declined` / `expired` / `withdrawn`).
54. Approvals are sequential — hiring manager, then admin — each recording approver,
    decision, notes, and timestamp. An approval already given stays attributed to its
    approver even if that person later loses access to the opening.
55. An approved offer generates a letter PDF from a template.
56. Offers past their expiry are moved to `expired` by a scheduled job, so the status is
    truthful without someone opening the record.
57. Accepting an offer sets the application to `hired`. An opening auto-closes once its
    hired count reaches its headcount.
58. Reporting: time-in-stage, time-to-hire, pass rate by stage, and interviewer load.

---

## Non-functional requirements

- **Data integrity over cleverness.** Nothing in the pipeline hard-deletes. Candidates,
  applications, and interviews soft-delete, and soft delete exists only for genuine
  mistakes — a duplicate entry, a typo'd record — not as an end-of-lifecycle step. Ending a
  candidate's lifecycle is **archiving**, which is separate, reversible, and loses nothing.
  The activity log is append-only and is never deleted.
- **Security / privacy.** Candidate documents on a private disk behind an authorization
  check. Compensation excluded from interviewer payloads by construction. Authorization
  enforced server-side in policies — never by hiding a button.
- **State machines are guarded in one place**, so the rules hold whether a call comes
  from a screen, a console command, or a test.
- **Performance.** Board and pipeline screens must not issue per-row queries. Target
  under 300 ms server time at our volume.
- **Accessibility.** WCAG 2.1 AA: keyboard-operable board and forms, labelled inputs,
  visible focus, and status never conveyed by colour alone.
- **Observability.** Failed background sends surface on the record. Error tracking is
  **Bugsnag** (already in use across this org's projects). Uptime and scheduler liveness
  are **Laravel Forge monitors plus a Forge heartbeat on the scheduler**, so a dead cron —
  which silently stops the Phase 3 expiry job and the Phase 2 digest — is noticed. Failed
  queue jobs are retained and reviewable; a job that exhausts its retries pages nobody but
  must appear on the record it belongs to.
- **Data retention.** Candidates are **never deleted and never scrubbed — they are
  archived.** An archived candidate keeps every field, every application, every scorecard,
  and its whole activity log; archiving only removes it from default lists and search, and
  it stays reachable behind an explicit "include archived" filter and by direct link.
  Archiving is reversible. It happens **automatically the moment a candidate holds no live
  application** — rejected, withdrawn, or hired — manually at any time, and it reverses
  automatically when a new application is opened for them. There is no retention timer and
  no scheduled purge job: the end of a candidate's last pipeline is the event, so nothing
  has to be swept up later. If a regulator ever requires real erasure, that is a separate,
  deliberate, logged operation.
- **Browser support.** Current Chrome, Safari, Firefox, Edge. Desktop-first; the
  interviewer's screens must be usable on a phone.

### Testing expectations

- **Transition tests are the backbone**: every legal and illegal state transition, plus
  the concurrent-decision guard.
- **Permission tests driven by the role matrix**: for each role × each surface, assert
  both what is allowed *and* what is refused. Refusals must be asserted explicitly —
  this is where a bug leaks compensation or candidate data.
- **Negative payload tests**: an interviewer's response must not contain comp fields or
  peer feedback.
- **One end-to-end test** walks a candidate from creation to hired across every stage.
  If that test needs editing, something structural changed and you want to notice.

---

## Success metrics

| Metric | Definition | Target / baseline |
|---|---|---|
| End-to-end usage | A real hire run entirely inside the system, no side channel | 1 by end of Phase 1 |
| Feedback capture | Interviews with a submitted scorecard ÷ interviews held | ≥ 90% |
| Feedback latency | Median hours from interview end to scorecard submitted | < 24h |
| Decision latency | Median days a stage sits with all feedback in and no decision | < 2 days |
| Stale candidates | Active applications past their stage threshold at any time | < 10% of in-flight |
| Coordination effort | Recruiter self-reported minutes per interview scheduled | Baseline measured in Phase 1; −50% after Phase 2 |
| Time-to-hire | Application created → offer accepted | Baseline measured in Phase 1; provisional target ≤ 30 days; reported from Phase 3 |

**On the two baselines.** They are measured, not guessed. Inventing a number to fill the
cell would make the −50% target meaningless. Phase 1 captures both as a side effect of
normal use: time-to-hire falls out of the activity log the moment the first application
reaches `hired`, and coordination effort comes from a single optional "minutes spent
scheduling" field on the interview record. Both baselines are fixed after **five completed
hires or sixty days of use, whichever comes first**, and written into this table then.

---

## Dependencies, constraints, and milestones

**Dependencies**

- Phase 1: none external. This is deliberate — it can be built and dogfooded with no
  credentials and no network access.
- Phase 2: a Google Workspace service account with domain-wide delegation, so events are
  created on the interviewer's calendar rather than from a personal OAuth grant that dies
  when that person leaves.
- Phase 2 email: **Resend in production, Laravel's `log` mailer in development.** Mail is
  composed for real in both — templates, placeholders, recipients, queueing, retries, and
  the send-failure surfacing all behave as specified; locally the message lands in the log
  instead of an inbox. Resend has a first-party Laravel driver, so the difference is a
  `MAIL_MAILER` value and an API key, not a code path. Development must **never** be
  pointed at Resend — a real rejection email sent to a real candidate from someone's laptop
  is the exact accident this arrangement prevents.
- Phase 2 sending identity: **From `hiring@<org-domain>`** with the return-path on a
  dedicated subdomain (`mail.<org-domain>`) carrying SPF, DKIM, and DMARC, so a hiring
  bounce cannot damage the primary domain's reputation. ⚠️ Confirm the exact domain and who
  owns its DNS before Phase 2 ships to production; nothing else about the decision changes.
- Phase 3: offer letters render with **`spatie/laravel-pdf`** (headless Chrome via
  Browsershot). Chosen over dompdf because the letter is a Blade view styled with the same
  Tailwind as the rest of the app, and dompdf does not render modern CSS. It needs Node and
  Chromium on the server — acceptable, and Forge provisions both. Rendering runs in a queued
  job; the generated PDF is written to the private disk and is immutable once approved.

**Constraints**

- Single organisation, internal users only. Do not build tenancy.
- External integrations must sit behind an interface from day one, with a
  no-op implementation in Phase 1, so Phase 2 is a swap rather than a retrofit.
- Background work runs on the database queue driver. Redis is unwarranted at this volume.

**Milestones**

| Phase | Contents | Done when |
|---|---|---|
| **1 — Internal spine** | Auth, roles, openings, both template types, candidates, applications, snapshotted stages, interviews, scorecards, decisions, board, pipeline table, dashboard, interviewer home, notifications, activity log | A real hire has been run end to end in the system |
| **2 — Outward-facing** | Calendar events, transactional email, candidate status page, staleness digest, send-failure surfacing | Coordinators stop sending interview details by hand |
| **3 — Offers & reporting** | Offer records, approval chain, letter PDF, expiry job, auto-close on fill, reporting | An offer is drafted, approved, sent, and accepted in the system |

**Dates.** Estimated for one engineer working steadily, anchored at kickoff on
**2026-08-12**. These are sequencing commitments, not deadlines handed to anyone else —
Phase 1's real gate is a completed hire, which depends on a live requisition, not on a date.

| Phase | Estimate | Target |
|---|---|---|
| 1a — Foundation: auth, users, four roles, admin user management, both template types | 2 weeks | 2026-08-26 |
| 1b — Pipeline core: openings, candidates, applications, snapshotted stages, decisions, board, pipeline table | 2 weeks | 2026-09-09 |
| 1c — Interviews: panels, reschedule/cancel/close-out, scorecards, interviewer home, notifications | 2 weeks | 2026-09-23 |
| 1d — Dashboard action queue, then hardening | 2 weeks | 2026-10-07 |
| **Phase 1 complete** | 8 weeks | **2026-10-07**, plus one real hire |
| Phase 2 — outward-facing | 3 weeks | 2026-10-28 |
| Phase 3 — offers & reporting | 4 weeks | 2026-11-25 |

Phase 2 does not start until a hire has actually run end to end in Phase 1. That gate is
the point of the sequencing and outranks the dates above.

**Sequencing rationale.** Two of the three stated pains — inconsistent evaluation and no
visibility — are solved entirely by Phase 1, which has no external dependencies. The
calendar integration then lands against a domain model that has survived real use rather
than a theoretical one. Trade-off accepted: throughout Phase 1 the coordinator still
communicates interview times manually. Scheduling data is recorded but not transmitted.

---

## Risks and open questions

### Risks

The failure modes that matter in a hiring system are not crashes — they are states that
quietly become wrong. Each of these is a requirement in disguise.

| Risk | Mitigation |
|---|---|
| A template is edited while candidates are mid-pipeline | Stages are copied onto the application at creation; template edits never reach an application in flight. Scorecard submissions snapshot their criteria for the same reason. |
| An interviewer leaves the company | Deactivate, never delete. Their scorecards and panel membership stay attributed. Deactivation warns about upcoming interviews needing reassignment but **never auto-unassigns** — silently emptying a panel loses the fact that they were on it. The deactivated member stays on the interview, rendered as inactive, until a recruiter explicitly removes or replaces them, which is logged. Removing them clears the scorecard they owed, so the stage can reach *Ready to decide*. |
| A hiring manager is reassigned on an opening | Allowed and logged. The previous manager loses access immediately; any approval already given stays recorded as theirs. |
| An internal candidate is also a user | Never assignable to their own interviews; their application is excluded from their own visibility. Enforced server-side. Matching is on **case-insensitive email**, plus an explicit optional link from candidate to user for the case where they applied with a personal address — email alone is not a reliable identity. |
| Two people decide the same stage at once | `SELECT … FOR UPDATE` on the application row inside the transaction, re-read the stage, and abort if it already carries a decision; the loser is told who decided it and what they decided. The application row — not the stage row — is the lock, so concurrent writes to two stages of one application also serialise. |
| Interviewer double-booking | Flagged, not blocked — we have no free/busy data. Phase 2 calendar integration improves this. |
| A candidate goes cold unnoticed | Per-stage staleness threshold surfaced on the board, the pipeline table, the dashboard, and (Phase 2) a digest. |
| A calendar invite or email silently fails to send | Queued with retries; a failed send is visible on the record in the UI. |
| Scope creep toward a full public ATS | The out-of-scope list above is a commitment, not a wish list. |

### Questions, resolved

Nothing here is open any more. Each was settled on 2026-08-12; the reasoning is kept so a
future reader can tell a decision from an accident.

- **Baseline time-to-hire and coordination effort.** Measured during Phase 1 rather than
  guessed — see the note under Success metrics. Both are fixed after five hires or sixty
  days.
- **Email provider and sending domain.** Resend in production, `log` mailer in development.
  The domain is still to be confirmed, which blocks nothing until Phase 2 deploys. See
  Dependencies.
- **Candidate status-page link validity.** The status link is signed and valid for
  **30 days**, and every candidate email issues a fresh one, so a candidate in a long
  pipeline is never locked out and an old forwarded link dies on its own. Interview-specific
  links expire **7 days after the interview's scheduled end**. An expired or superseded link
  responds **`410 Gone`** — the truthful status for something that existed and no longer
  does — with a plain human page explaining which it was (expired, or rescheduled and
  superseded). Not a 404, not a 200, and never a login wall. Links are per-application and
  carry no enumerable id.
- **Retention for rejected candidates.** Archive as soon as no live application remains —
  rejected, withdrawn, or hired alike. Never delete, never scrub, no retention timer.
  Reversible by hand, and reversed automatically when the person applies again. See
  Non-functional requirements.
- **A "who owes me feedback" chase list for recruiters.** No — not a fifth surface. The
  existing per-stage "not yet submitted" indicator plus the dashboard's *Ready to decide*
  signal already answer it, and a dedicated screen would be a chase list nobody opens.
  Outstanding feedback instead rides along in the **Phase 2 staleness digest**, which is
  already going to the owning recruiter. Revisit only if a real recruiter asks for it twice.

---

## Decisions already made

Recorded so they are not silently re-litigated mid-build.

| Decision | Chosen | Rejected |
|---|---|---|
| Audience | Single-org internal tool | Multi-tenant SaaS |
| Pipeline entry | Starts after screening | Public application form and intake |
| Pipeline structure | Templates, copied per application | Fixed global stages; fully per-role config |
| Scheduling | Manual slot entry + notifications, then calendar push | Candidate self-booking; two-way sync |
| Evaluation | Scorecard template per stage, fixed 1–4 scale | Free-form notes; per-criterion scales |
| Candidate surface | Email plus a signed status link | Email only; a full candidate portal |
| Person modelling | Candidate and application as separate records | One collapsed record |
| Offer scope | Record + approval chain + generated letter | Record only; e-signature |
| Dashboard | Action queue — what needs *me* now | Metrics overview |
| Sequencing | Walking skeleton first | One big v1; scheduling-first |

Settled on 2026-08-12, closing every `[TBD]` and open question above:

| Decision | Chosen | Rejected |
|---|---|---|
| Authorization | Laravel policies and gates, roles as a fixed enum on a pivot | `spatie/laravel-permission` — permissions are never edited at runtime, and the hard rules (hiring-manager scoping, never see your own application) are per-entity query logic a permission table cannot express |
| State machines | Plain PHP transition classes, one per aggregate, invoked only through actions | A state-machine package; transition logic scattered across controllers |
| Error tracking | Bugsnag | Flare; nothing |
| Uptime & scheduler liveness | Forge monitors plus a heartbeat on the scheduler | Trusting cron silently |
| Transactional email (Phase 2) | Resend in production, `log` mailer in development | Postmark/SES/Mailgun; letting a dev environment send to real candidates |
| Calendar auth (Phase 2) | Workspace service account with domain-wide delegation | Per-user OAuth grants that expire when someone leaves |
| Status-link lifetime (Phase 2) | 30 days, reissued with every email; interview links 7 days past the interview; expired responds `410 Gone` | One permanent link; a login wall; a 404 that hides what happened |
| Offer letter PDF (Phase 3) | `spatie/laravel-pdf` (headless Chrome) | dompdf — cannot render the app's Tailwind |
| Candidate retention | Archive as soon as no live application remains — reversible, nothing scrubbed, no timer | A 24-month retention job; hard purge; anonymising in place, which destroys the record you would want in a dispute; a per-status list of archive triggers that drifts |
| Recruiter chase list | Fold outstanding feedback into the Phase 2 digest | A fifth screen nobody opens |
| Metric baselines | Measure during Phase 1, fix after 5 hires or 60 days | Inventing a number to fill the cell |
