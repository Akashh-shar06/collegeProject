<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Certificate - <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #525659; padding: 40px 0; }
        .tc-document {
            background: #FFFFFF;
            width: 800px;
            margin: 0 auto;
            padding: 50px;
            border: 8px double #1E293B;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .tc-header { text-align: center; border-bottom: 2px solid #1E293B; padding-bottom: 20px; margin-bottom: 30px; }
        .tc-header h1 { font-size: 26px; text-transform: uppercase; margin-bottom: 4px; color: #0F172A; }
        .tc-header h3 { font-size: 18px; font-weight: normal; color: #475569; }
        .tc-title { text-align: center; font-size: 22px; font-weight: bold; text-decoration: underline; margin: 30px 0; }
        .tc-body { font-size: 16px; line-height: 2; margin-bottom: 50px; }
        .tc-field { font-weight: bold; text-decoration: underline; padding: 0 5px; }
        .tc-signatures { display: flex; justify-content: space-between; margin-top: 80px; }
        .sig-box { text-align: center; border-top: 1px solid #000; width: 200px; padding-top: 6px; font-size: 14px; font-weight: bold; }
        @media print {
            body { background: #FFF; padding: 0; }
            .tc-document { box-shadow: none; width: 100%; border: 4px double #000; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="width:800px; margin:0 auto 20px auto; text-align:right;">
    <button type="button" onclick="window.print()" style="padding:10px 20px; font-size:14px; background:#0066FF; color:#FFF; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">🖨️ Print Transfer Certificate</button>
</div>

<div class="tc-document">
    <div class="tc-header">
        <h1><?= htmlspecialchars($user['tenant_name'] ?? 'CAMPUS360 COLLEGE INSTITUTION', ENT_QUOTES, 'UTF-8') ?></h1>
        <h3>Official Academic Transfer Certificate</h3>
        <p style="font-size:12px; margin-top:6px;">TC No: <strong>TC-<?= date('Y') ?>-<?= str_pad((string)$student['id'], 4, '0', STR_PAD_LEFT) ?></strong> | Date: <strong><?= date('d M Y') ?></strong></p>
    </div>

    <div class="tc-title">TRANSFER CERTIFICATE</div>

    <div class="tc-body">
        This is to certify that Student <span class="tc-field"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'], ENT_QUOTES, 'UTF-8') ?></span>, 
        Admission No <span class="tc-field"><?= htmlspecialchars($student['admission_no'], ENT_QUOTES, 'UTF-8') ?></span>, 
        Roll No <span class="tc-field"><?= htmlspecialchars($student['roll_no'], ENT_QUOTES, 'UTF-8') ?></span>, 
        Son/Daughter of <span class="tc-field"><?= htmlspecialchars($student['guardian_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>, 
        was a student of this institution in <span class="tc-field"><?= htmlspecialchars($student['batch_name'], ENT_QUOTES, 'UTF-8') ?></span>.
        <br><br>
        His/Her Date of Birth recorded in the official admission register is <span class="tc-field"><?= date('d F Y', strtotime($student['dob'])) ?></span>.
        He/She has completed all academic requirements, cleared all financial dues, and holds a conduct evaluation of <strong>GOOD</strong>.
        <br><br>
        His/Her student status is officially marked as <strong>TRANSFERRED / GRADUATED</strong> as of <?= date('d F Y') ?>.
    </div>

    <div class="tc-signatures">
        <div class="sig-box">Prepared By (Registrar)</div>
        <div class="sig-box">Checked By (Accountant)</div>
        <div class="sig-box">Principal / Seal</div>
    </div>
</div>

</body>
</html>
