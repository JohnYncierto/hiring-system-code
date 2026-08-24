# Hiring System

An internal applicant tracking system for a single organisation, covering the hiring
pipeline from the first post-screening interview through to an accepted offer.

Staff-facing only — candidates never log in. Four kinds of user (admin, recruiter, hiring
manager, interviewer), and the system's job is to make interview coordination boring,
evaluation comparable, and every past hiring decision reconstructible.

**[`PRD.md`](./PRD.md) is the source of truth** for scope, roles, permissions, data rules,
and phasing. Read it before writing code. Everything below is orientation, not requirements.

## Status

**Greenfield — not yet scaffolded.** The repository currently contains the PRD and this
file. Phase 1 is the build target.

## Stack

| | |
|---|---|
| Backend | Laravel 13, PHP |
| Frontend | Inertia 3, Vue 3 (TypeScript), Tailwind |
| Database | MySQL |
| Queue | Database driver — Redis is unwarranted at this volume |
| Tests | Pest |
| Local serving | Laravel Herd → `https://hiring-system.test` |

## Getting started

Once the app is scaffolded:

```bash
composer install
npm install
cp .env.example .env        # then fill in the DB credentials
php artisan key:generate
php artisan migrate --seed
npm run dev
```

Herd serves the site at `https://hiring-system.test` — no `artisan serve` needed.

```bash
php artisan test             # Pest
vendor/bin/pint --dirty      # format PHP before committing
```

## Phases

| Phase | Contents | Done when |
|---|---|---|
| **1 — Internal spine** | Auth, roles, openings, both template types, candidates, applications, snapshotted stages, interviews, scorecards, decisions, board, pipeline table, dashboard, interviewer home, notifications, activity log | A real hire has been run end to end in the system |
| **2 — Outward-facing** | Calendar events, transactional email, candidate status page, staleness digest, send-failure surfacing | Coordinators stop sending interview details by hand |
| **3 — Offers & reporting** | Offer records, approval chain, letter PDF, expiry job, auto-close on fill, reporting | An offer is drafted, approved, sent, and accepted in the system |

Phase 1 has **no external dependencies** — no Google credentials, no email provider, no
network access. That is deliberate; keep it that way. Phase 2 does not start until a hire
has actually run end to end in Phase 1.

Phase 1 is built in four slices: **1a** foundation (auth, users, roles, templates) →
**1b** pipeline core (openings, candidates, applications, stages, decisions, board, pipeline
table) → **1c** interviews and scorecards → **1d** the dashboard action queue. The activity
log is not a slice; it is written by every state change from 1a onward.

## Design rules that are not up for debate

These are argued out in the PRD's decisions log. Changing one is a conversation, not a
refactor.

- **Candidate and Application are separate records.** One person may apply twice, or to two
  openings at once.
- **Pipeline stages are copied onto an application at creation**, never referenced live.
  Editing a template must not move a candidate who is mid-pipeline.
- **Scorecard criteria are snapshotted onto each submission**, for the same reason.
- **All criteria use a fixed 1–4 scale.** Varying scales make submissions incomparable.
- **Compensation is never present in an interviewer's response payload.** Build a different
  object for them — do not hide fields in the UI.
- **Nobody sees their own application**, admins included, and nobody is assignable to their
  own interviews. Enforced server-side.
- **Nothing hard-deletes, and candidates are archived rather than removed.** A candidate is
  archived automatically once no live application remains — rejected, withdrawn, or hired —
  and re-applying un-archives them. Archiving is a reversible visibility state that scrubs
  nothing. Soft deletes exist for genuine mistakes only. The activity log is append-only.
- **External integrations sit behind an interface from day one**, with no-op implementations
  in Phase 1, so Phase 2 is a swap rather than a retrofit.
- **The dashboard reports no metrics.** It is an action queue. Reporting is Phase 3.
- **Authorization lives in policies**, never in a hidden button. State transitions live in
  one guarded place per aggregate, so the rules hold from a screen, a console command, or a
  test.

## Testing

Where the risk actually is, per the PRD:

- Every legal **and illegal** state transition, plus the concurrent-decision guard.
- The permission matrix, role by role and surface by surface — asserting refusals
  explicitly, not just successes. This is where a bug leaks compensation or candidate data.
- Negative payload assertions: an interviewer's response contains no comp fields and no peer
  feedback.
- One end-to-end test walking a candidate from creation to hired. If it needs editing,
  something structural changed and you want to notice.

## Conventions

- Never commit or push without asking.
- Run `vendor/bin/pint --dirty` after touching PHP.
- Never run `migrate:fresh`, `migrate:refresh`, or `db:wipe`. To re-test a migration:
  `php artisan migrate:rollback --step=1` then `php artisan migrate`.
- Every change gets a test.
