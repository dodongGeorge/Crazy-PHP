<?php
/*
 * DEPARTMENT OF UNNECESSARY PERMISSION (D.U.P.)
 * Apply for official permission to do things you never needed permission for.
 * Requires PHP 8+ (uses str_contains and match).
 */
session_start();

// Start over (not recommended, says the website)
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// ---------- DATA (arrays) ----------
$departments = [
    'Bureau of Pre-Approvals',
    'Office of Second Opinions',
    'Ministry of Forms About Forms',
    'Division of Stamps (Ink Only)',
    'Committee on Committees',
    'Department of Lunch',
];

$attempts = $_SESSION['attempts'] ?? 0;   // int: how many times the applicant has suffered here
$attemptNo = $attempts + 1;
$errors = [];
$slip = null;       // filled only when the application is processed
$debug = [];        // "under the hood" panel for the presentation

// ---------- FORM HANDLING ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');                  // string
    $activity   = trim($_POST['activity'] ?? '');              // string
    $age        = (int) ($_POST['age'] ?? 0);                  // int
    $rawScale   = (int) ($_POST['urgency'] ?? 5);              // int
    $reason     = $_POST['reason'] ?? 'vibes';                 // string
    $triedNot   = isset($_POST['tried']);                      // bool

    // --- Validation (if / elseif) ---
    if ($name === '') {
        $errors[] = 'Name missing. Please resubmit under a different name.';
    }
    if ($activity === '') {
        $errors[] = 'No activity detected. Doing nothing does not require a permit (Form 0-0).';
    }
    if ($age <= 0 || $age > 130) {
        $errors[] = 'Age rejected. Humans are not yet approved for that range.';
    }

    $_SESSION['attempts'] = $attemptNo; // every try counts, even bad ones

    if (empty($errors)) {
        // --- Analysis of the activity (string functions + loop) ---
        $lower   = strtolower($activity);
        $words   = str_word_count($activity);
        $letters = strlen(preg_replace('/[^a-z]/i', '', $activity));
        $vowels  = 0;
        foreach (str_split($lower) as $ch) {
            if (in_array($ch, ['a', 'e', 'i', 'o', 'u'])) {
                $vowels++;
            }
        }

        // The scale is 1 = most urgent, 10 = least. Nobody reads Annex C.
        $urgency = 11 - $rawScale;

        // --- Fee calculation (float math) ---
        $fee = 25.00 + ($words * 13.50) + ($vowels * 2);
        if ($urgency > 7) {
            $fee += 200;                 // urgency surcharge
        }
        if (!$triedNot) {
            $fee += 99;                  // fee for not trying "not doing it" first
        }
        $fee *= 1 + (0.15 * $attempts);  // repeat-applicant surcharge
        $fee = round($fee, 2);

        // --- Stamps required (for loop) ---
        $stampsNeeded = min(2 + intdiv($letters, 6), 12);
        $stamps = [];
        for ($i = 1; $i <= $stampsNeeded; $i++) {
            $dept = $departments[($i - 1) % count($departments)];
            $window = ($i * 7 + $attempts) % 23 + 1;
            $stamps[] = "Stamp $i of $stampsNeeded: $dept, Window $window";
        }

        // --- Queue simulation (while loop) ---
        $queue = 40 + ($attempts * 13) + $words;
        $served = 1;
        $ticker = [];
        while ($served < $queue && count($ticker) < 4) {
            $ticker[] = "Now serving #$served";
            $served += intdiv($queue, 4);
        }

        // --- The Decision (if / elseif / else + switch) ---
        $score = ($letters + $age + $vowels) % 4;

        if ($attemptNo >= 5) {
            $status = 'ESCALATED';
            $note = "You have applied $attemptNo times. Your case has been forwarded to the Supervisor's Supervisor, who is on a sabbatical until further notice.";
        } elseif ($age < 18) {
            $status = 'DENIED';
            $note = 'Applicants under 18 need parental permission, which requires its own permit (Form 27-B), which requires you to be 18.';
        } elseif (str_contains($lower, 'nap') || str_contains($lower, 'sleep')) {
            $status = 'DENIED';
            $note = 'You are not tired enough. Tiredness must be notarized by two witnesses who are also tired.';
        } elseif (str_contains($lower, 'eat') || str_contains($lower, 'snack') || str_contains($lower, 'food')) {
            $status = 'PENDING';
            $note = 'Snack authorization belongs to the Department of Lunch. They are currently at lunch.';
        } elseif (str_contains($lower, 'quit') || str_contains($lower, 'resign')) {
            $status = 'APPROVED';
            $note = 'Approved in 0.2 seconds. This is suspicious. An audit has been opened on you.';
        } elseif (str_contains($lower, 'love') || str_contains($lower, 'date')) {
            $status = 'DENIED';
            $note = 'Feelings are not covered under Section 9. Please resubmit as a spreadsheet.';
        } else {
            switch ($score) {
                case 0:
                    $status = 'APPROVED';
                    $note = 'Approved, on the condition that you do not enjoy it.';
                    break;
                case 1:
                    $status = 'DENIED';
                    $note = 'Denied due to Mercury being in a mood.';
                    break;
                case 2:
                    $status = 'PENDING';
                    $note = 'Pending the results of a study that has not been commissioned yet.';
                    break;
                default:
                    $status = 'APPROVED';
                    $note = 'Approved retroactively. You were already doing it last Tuesday, which was illegal.';
            }
        }

        // --- Remark based on stated reason (match expression) ---
        $remark = match ($reason) {
            'boredom' => 'Reason noted: boredom. The Department respects a fellow sufferer.',
            'spite'   => 'Reason noted: spite. Strong motivation. Fee unchanged out of spite.',
            default   => 'Reason noted: "vibes". Vibes are not admissible, but we wrote it down.',
        };

        $formNo = 'DUP-' . (100 + $attempts * 7) . '-' . strtoupper(substr(md5($activity), 0, 4));

        $slip = compact('name', 'activity', 'status', 'note', 'remark', 'fee', 'stamps', 'ticker', 'queue', 'formNo');

        // --- Under the hood table (variable names, values, data types) ---
        $debug = [
            '$name'         => $name,
            '$age'          => $age,
            '$rawScale'     => $rawScale,
            '$urgency'      => $urgency,
            '$triedNot'     => $triedNot,
            '$words'        => $words,
            '$vowels'       => $vowels,
            '$fee'          => $fee,
            '$stampsNeeded' => $stampsNeeded,
            '$score'        => $score,
            '$departments'  => $departments,
            '$attemptNo'    => $attemptNo,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Department of Unnecessary Permission</title>
<style>
  :root {
    --paper: #dfe9dc;
    --paper-dark: #c7d6c3;
    --ink: #1d3557;
    --stamp: #b3261e;
    --muted: #4d5f55;
    --field: #f4f8f1;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    background: #8da08f;
    color: var(--ink);
    font-family: "Courier New", Courier, monospace;
    line-height: 1.5;
    padding: 24px 12px 60px;
  }
  .sheet {
    max-width: 760px;
    margin: 0 auto;
    background: var(--paper);
    border: 3px double var(--ink);
    padding: 28px 32px 36px;
    box-shadow: 8px 8px 0 rgba(29, 53, 87, .25);
  }
  h1 { font-size: 1.5rem; margin: 0 0 4px; line-height: 1.2; }
  .sub { margin: 0 0 18px; color: var(--muted); }
  .formno { float: right; font-size: .8rem; color: var(--muted); }
  fieldset { border: 1px solid var(--ink); margin: 0 0 16px; padding: 12px 16px 16px; }
  legend { padding: 0 8px; font-weight: bold; }
  label { display: block; margin-top: 10px; font-weight: bold; }
  .tiny { font-size: .68rem; font-weight: normal; color: var(--muted); }
  input[type=text], input[type=number], select {
    width: 100%; padding: 8px; margin-top: 4px;
    background: var(--field); border: 1px solid var(--ink);
    font: inherit; color: var(--ink);
  }
  .radios label, .check { font-weight: normal; display: inline-block; margin-right: 14px; }
  .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
  button {
    font: inherit; font-weight: bold; padding: 10px 18px; cursor: pointer;
    border: 2px solid var(--ink);
  }
  button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, summary:focus-visible {
    outline: 3px solid var(--stamp); outline-offset: 2px;
  }
  .real { background: var(--ink); color: #fff; }
  .decoy { background: var(--field); color: var(--ink); }
  .errors { border: 2px solid var(--stamp); background: #f6dedb; padding: 10px 16px; margin-bottom: 16px; }
  .errors ul { margin: 6px 0 0; padding-left: 20px; }
  .slip { border: 2px dashed var(--ink); padding: 18px 22px; margin-bottom: 22px; background: var(--paper-dark); position: relative; }
  .slip h2 { margin: 0 0 8px; font-size: 1.15rem; }
  .stamp {
    display: inline-block; border: 4px solid var(--stamp); color: var(--stamp);
    padding: 2px 14px; font-size: 1.6rem; font-weight: bold;
    transform: rotate(-6deg); margin: 8px 0 10px;
    animation: thunk .35s ease-out;
  }
  @keyframes thunk { from { transform: rotate(-6deg) scale(2.2); opacity: 0; } to { transform: rotate(-6deg) scale(1); opacity: 1; } }
  @media (prefers-reduced-motion: reduce) { .stamp { animation: none; } }
  .slip ul { margin: 6px 0 12px; padding-left: 20px; }
  .fee { font-size: 1.2rem; font-weight: bold; }
  details { margin-top: 20px; border-top: 1px solid var(--ink); padding-top: 10px; }
  summary { cursor: pointer; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: .82rem; }
  th, td { border: 1px solid var(--ink); padding: 4px 8px; text-align: left; vertical-align: top; }
  .foot { margin-top: 18px; font-size: .8rem; color: var(--muted); }
  .foot a { color: var(--ink); }
  @media (max-width: 520px) { .sheet { padding: 20px 16px 28px; } .formno { float: none; display: block; } }
</style>
</head>
<body>
<main class="sheet">
  <span class="formno">Form DUP-<?= 100 + $attempts * 7 ?> / Attempt #<?= $attemptNo ?></span>
  <h1>Department of Unnecessary Permission</h1>
  <p class="sub">Get official approval for things you were always allowed to do.</p>

  <?php if (!empty($errors)): ?>
    <div class="errors" role="alert">
      <strong>Your application was rejected before it was read:</strong>
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($slip): ?>
    <section class="slip" aria-live="polite">
      <h2>Official Response, Form <?= e($slip['formNo']) ?></h2>
      <p>Applicant: <strong><?= e($slip['name']) ?></strong><br>
         Requested permission to: <strong><?= e($slip['activity']) ?></strong></p>

      <div class="stamp"><?= e($slip['status']) ?></div>
      <p><?= e($slip['note']) ?></p>
      <p><?= e($slip['remark']) ?></p>

      <p class="fee">Processing fee: ₱<?= number_format($slip['fee'], 2) ?></p>

      <strong>Stamps you must collect first:</strong>
      <ul>
        <?php foreach ($slip['stamps'] as $line): ?>
          <li><?= e($line) ?></li>
        <?php endforeach; ?>
      </ul>

      <strong>Queue status:</strong>
      <ul>
        <?php foreach ($slip['ticker'] as $line): ?>
          <li><?= e($line) ?></li>
        <?php endforeach; ?>
        <li><strong>Your number: #<?= (int) $slip['queue'] ?></strong></li>
      </ul>
      <p class="tiny">To appeal, submit a new application. Appeals cost 15% more each time.</p>
    </section>
  <?php endif; ?>

  <form method="post" action="index.php">
    <fieldset>
      <legend>Section A: The Applicant</legend>
      <label for="name">Full legal name (or the one you regret least)</label>
      <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>">

      <label for="age">Age in years <span class="tiny">(no decimals, we checked)</span></label>
      <input type="number" id="age" name="age" value="<?= e($_POST['age'] ?? '') ?>">
    </fieldset>

    <fieldset>
      <legend>Section B: The Thing</legend>
      <label for="activity">I request permission to:</label>
      <input type="text" id="activity" name="activity" placeholder="e.g. rearrange my sock drawer" value="<?= e($_POST['activity'] ?? '') ?>">

      <label for="urgency">Urgency (1-10) <span class="tiny">Scale defined in Annex C. Annex C is not available.</span></label>
      <select id="urgency" name="urgency">
        <?php for ($n = 1; $n <= 10; $n++): ?>
          <option value="<?= $n ?>" <?= (($_POST['urgency'] ?? '5') == $n) ? 'selected' : '' ?>><?= $n ?></option>
        <?php endfor; ?>
      </select>

      <label>Why?</label>
      <div class="radios">
        <?php foreach (['vibes' => 'Vibes', 'boredom' => 'Boredom', 'spite' => 'Spite'] as $val => $label): ?>
          <label><input type="radio" name="reason" value="<?= $val ?>" <?= (($_POST['reason'] ?? 'vibes') === $val) ? 'checked' : '' ?>> <?= $label ?></label>
        <?php endforeach; ?>
      </div>

      <label class="check">
        <input type="checkbox" name="tried" <?= isset($_POST['tried']) ? 'checked' : '' ?>>
        I have already tried not doing it. <span class="tiny">(Skipping this adds a fee.)</span>
      </label>
    </fieldset>

    <div class="actions">
      <button type="button" class="decoy" onclick="alert('The Submit window is closed for renovation. Please use the Cancel button.')">Submit</button>
      <button type="submit" class="real">Cancel Application</button>
    </div>
  </form>

  <p class="foot">
    Applicants processed this session: <?= $attempts ?>.
    <a href="?reset=1">Start over</a> (this will make us suspicious).
  </p>

  <?php if (!empty($debug)): ?>
    <details>
      <summary>Under the hood (for the presentation)</summary>
      <table>
        <tr><th>Variable</th><th>Value</th><th>Data type</th></tr>
        <?php foreach ($debug as $var => $val): ?>
          <tr>
            <td><?= e($var) ?></td>
            <td><?= is_array($val) ? e(implode(', ', $val)) : e(var_export($val, true)) ?></td>
            <td><?= e(gettype($val)) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </details>
  <?php endif; ?>
</main>
</body>
</html>
