# Personal XP (`local_personalxp`)

Personal XP adds a non-competitive experience-point system to Moodle. XP represents the learner's own progress through a
course. The plugin intentionally does **not** provide leaderboards, podiums, leagues, public rankings or messages
comparing one learner with another.

## Why Personal XP has no competition

Personal XP was intentionally designed without leaderboards, rankings, podiums, leagues or any other mechanism that
turns learner progress into a race.

This is not a missing feature. It is a design decision.

Gamification can be useful in education, but gamification and competition are not the same thing. A leaderboard may
motivate a learner who is already close to the top while repeatedly sending a very different message to someone who is
struggling, started later, has less time available, has accessibility barriers or simply learns at another pace: **you
are behind everyone else**.

When that happens, XP stops representing progress and starts representing social status.

Educational motivation is more complex than adding points and sorting a table. Research on motivation and gamification
shows that autonomy, competence, meaningful progress and intrinsic motivation matter for persistence, while public
comparison and external rewards can produce very different results depending on the learner and on how the mechanic is
implemented. Some studies of educational leaderboards have found no participation benefit and have reported lower
motivation, satisfaction, empowerment or academic performance in particular contexts.

This does not mean that every form of competition is harmful, nor that a leaderboard automatically causes course
abandonment. The evidence is more nuanced than that, and well-designed gamification can improve engagement and
retention. The mistake is assuming that competition motivates everybody in the same way.

That assumption is especially risky in online learning. A discouraged learner does not need to announce that they are
quitting; they can simply stop returning to the course. A mechanic created to energize the most active students should
not make another group feel that catching up is impossible or that their progress is insignificant.

Personal XP therefore follows a different principle:

> The learner competes only with their own previous progress.

XP represents effort, participation and progression. Levels make progress visible. The history explains where the XP
came from. There is no first place and there is no last place.

A learner with 500 XP does not need to know that another learner has 5,000 XP. That information contributes nothing to
the first learner's learning process.

The goal is simple: use gamification to reinforce progress, not social status, and give learners one more reason to
continue rather than another reason to leave.

## Features

- XP for activity completion.
- XP for course completion.
- XP for quiz attempt submission, independent from grade.
- XP for forum participation with a configurable daily cap to discourage point farming.
- Idempotent awards: the same source cannot accidentally award the same XP twice.
- Per-course XP totals.
- Configurable levels using a simple `XP|Name` format.
- Learner dashboard with current level, progress to the next level and recent XP history.
- Teacher report ordered by learner name, not by XP.
- Moodle Privacy API integration, including export and deletion of user data.
- English and Brazilian Portuguese language packs.
- No leaderboard, podium, league or learner-to-learner ranking.

## Default rules

| Action                | Default XP |
|-----------------------|-----------:|
| Complete an activity  |      20 XP |
| Create a forum post   |       5 XP |
| Submit a quiz attempt |      10 XP |
| Complete the course   |     200 XP |

Forum XP is capped at 20 XP per course per day by default. All values can be changed in Site administration.

XP is intentionally separate from grades. XP can represent participation and effort while grades continue to represent
academic assessment. Submitting a quiz can therefore award XP without implying that the learner answered it correctly.

## Configuration

Go to:

```text
Site administration > Plugins > Local plugins > Personal XP
```

Levels are configured one per line:

```text
0|Beginner
100|Apprentice
300|Explorer
700|Practitioner
1500|Specialist
3000|Master
```

The learner sees only their own XP and progress. Teachers with the report capability can see the course participants and
their current XP for pedagogical follow-up, but the report deliberately does not sort them into a ranking.

## Data model

`local_personalxp_log` is the immutable award history. Each award stores the user, course, source rule, event, object,
amount, label and a SHA-256 unique source hash used to prevent duplicate awards.

`local_personalxp_user` stores the aggregated XP total for each user/course pair, avoiding repeated `SUM()` operations
over a potentially large history table.

## Adaptive activity XP with AI Bridge

For adaptive scoring, `local_personalxp` uses `local_ai_bridge` through the purpose:

\`personalxp-assess-activity\`

When a course module is created or relevant content changes, Personal XP stores a deterministic local estimate
immediately and queues an ad hoc AI refinement. AI only estimates cognitive difficulty and realistic effort; PHP owns
the XP formula and clamps the final value to the configured minimum and maximum.

With the defaults, XP is calculated from estimated minutes at 2 XP/minute and a difficulty multiplier from 1.00 to 2.80.
For example, a two-minute easy Page is 4 XP, while a 20-minute difficulty-5 activity is 112 XP.

Page reading time is calculated locally from text length and cannot be inflated by AI. Quiz scoring is recalculated when
quiz slots are created, deleted or switched to another question revision, so a quiz is not permanently scored while it is
still empty.
