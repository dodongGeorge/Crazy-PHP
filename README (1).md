# Department of Unnecessary Permission (D.U.P.)

A deliberately useless PHP web app where you apply for official permission to do things you never needed permission for, like rearranging your sock drawer. The Department answers with a stamp, a fee, a queue number, and a reason that makes no sense.

Built as a group activity to practice PHP fundamentals (variables, data types, form handling, control structures, and loops) with a creative twist.

## How it works

1. Fill out the application form (name, age, what you want to do, urgency, reason).
2. Submit it (the right button is not the one labeled "Submit").
3. PHP validates the input, calculates a fee, builds a list of required stamps, simulates a queue, and issues a verdict: **Approved**, **Denied**, **Pending**, or **Escalated**.
4. Appeal as many times as you like. Each retry costs 15% more, and after five attempts your case is escalated to "the Supervisor's Supervisor, who is on sabbatical."

Some sample verdicts:

- "Denied due to Mercury being in a mood."
- "Approved retroactively. You were already doing it last Tuesday, which was illegal."
- "Snack authorization belongs to the Department of Lunch. They are currently at lunch."

## Intentionally bad UX

| Feature | Why it's bad |
|---|---|
| Decoy "Submit" button | It only shows a JavaScript alert. The real submit button says "Cancel Application." |
| Reversed urgency scale | 1 is the most urgent, but the label only says "defined in Annex C" (which doesn't exist). |
| Vague fine print | Fees and rules are explained in tiny, unhelpful text. |
| Fee for not trying first | Skipping "I have already tried not doing it" adds to the fee. |

## PHP concepts used

| Concept | Where it appears in `index.php` |
|---|---|
| Variables and data types | `$name` (string), `$age` (int), `$triedNot` (bool), `$fee` (float), `$departments` (array) |
| Form handling | `$_POST`, `trim()`, `(int)` casting, `isset()` for the checkbox |
| `if` / `elseif` / `else` | Validation and the decision block (age, keywords, escalation) |
| `switch` | Fallback decision using `$score` |
| `match` | Remark based on the stated reason (vibes, boredom, spite) |
| `for` loop | Builds the list of required stamps |
| `foreach` loop | Counts vowels, prints stamps, builds the debug table |
| `while` loop | Queue ticker ("Now serving #...") |
| Sessions | `$_SESSION['attempts']` makes each retry costlier |
| Security | `htmlspecialchars()` through the `e()` helper |

After a successful submit, open the **Under the hood** section on the page to see each variable's value and its type from `gettype()`.

## Requirements

- PHP 8.0 or newer (the code uses `str_contains()` and `match`)
- A web server, or PHP's built-in server

## Run it

### Option 1: PHP built-in server

```bash
git clone https://github.com/<your-username>/<your-repo>.git
cd <your-repo>
php -S localhost:8000
```

Then open http://localhost:8000.

### Option 2: XAMPP

1. Install XAMPP with PHP 8 or newer and start **Apache** from the Control Panel.
2. Copy this project folder into `C:\xampp\htdocs\dup`.
3. Open http://localhost/dup/ in your browser.

### Troubleshooting

- **Parse error mentioning `match` or `str_contains`:** your PHP version is too old. Upgrade to PHP 8 or newer.
- **Raw PHP code shown in the browser:** you opened the file directly (`file://`). Open it through `localhost` instead.
- **Apache won't start in XAMPP:** another program is probably using port 80. Close it or change Apache's port.

## Try these inputs

| Input | Result |
|---|---|
| Empty form | Validation errors |
| Age `15`, activity `go outside` | Denied, needs parental permission |
| Activity `take a nap` | Denied, "not tired enough" |
| Activity `eat a snack` | Pending, the Department of Lunch is at lunch |
| Activity `quit my job` | Approved in 0.2 seconds, audit opened |
| Urgency `1` vs `10` | Different fees, because the scale is reversed |
| Submit 5 times | Case escalated, fee keeps climbing |

## Project structure

```
.
├── index.php                # The whole app: form, PHP logic, and styling
├── DUP_Presentation.pdf     # Presentation guide
└── README.md
```

## Notes

Attempt counts are stored in the PHP session. Use the **Start over** link at the bottom of the page to reset them (the Department will be suspicious).

This project is for learning and entertainment. Please do not use it to request actual permission for anything.
