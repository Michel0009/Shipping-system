<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عقد انضمام سائق</title>
    <style>
        @page {
            header: page-header;
            footer: page-footer;
        }
        body {
            font-family: 'cairo', sans-serif;
            color: #111827;
            font-size: 13.5px;
            line-height: 1.8;
            background-color: #ffffff;
        }
        .document-border {
            border: 3px double #1e3a8a;
            padding: 25px;
            min-height: 95%;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            text-align: center;
            font-size: 12px;
            color: #4b5563;
            margin-top: 5px;
            font-weight: bold;
        }

        .intro-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: justify;
        }

        .party-block {
            margin-bottom: 20px;
        }
        .party-header {
            background-color: #1e3a8a;
            color: white;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 3px 3px 0 0;
        }
        .party-body {
            border: 1px solid #1e3a8a;
            border-top: none;
            padding: 12px 15px;
            text-align: justify;
            border-radius: 0 0 3px 3px;
        }
        .label { font-weight: bold; color: #1e3a8a; }
        .val { color: #111827; margin-left: 10px; }
        .separator { color: #cbd5e1; margin: 0 8px; }

        .terms-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px dashed #94a3b8;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .term-item {
            margin-bottom: 12px;
            text-align: justify;
            padding-right: 15px;
            position: relative;
        }
        .term-number {
            font-weight: bold;
            color: #1e3a8a;
        }

        .signature-section {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-td {
            width: 50%;
            vertical-align: top;
            padding: 20px;
            padding-bottom: 50px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .sig-title {
            font-weight: bold;
            color: #1e3a8a;
            font-size: 15px;
            margin-bottom: 30px;
            text-decoration: underline;
        }
        .sig-line {
            margin-bottom: 20px;
            text-align: right;
        }
    </style>
</head>
<body>

<div class="document-border">

    <table class="header-table">
        <tr>
            <td width="33%" style="text-align: right; font-size: 12px; color: #64748b;">
                تاريخ الإصدار: {{ $contract_date }}<br>
            </td>
            <td width="34%">
                <h1 class="header-title">عقد تقديم خدمات لوجستية</h1>
                <div class="header-subtitle">وثيقة اعتماد وانضمام لشبكة النقل الرسمية</div>
            </td>
            <td width="33%" style="text-align: left;">
                <strong style="color: #1e3a8a; font-size: 18px;">Transnet Logistics</strong>
            </td>
        </tr>
    </table>

    <div class="intro-box">
        إنه في يوم الموافق لـ <strong>{{ $contract_date }}</strong>، تم الاتفاق والتراضي التام بين الأطراف المذكورة أدناه، وهم بكامل الأهلية القانونية والشرعية للتعاقد، على إبرام هذا العقد وفقاً للبنود والشروط الموضحة:
    </div>

    <div class="party-block">
        <div class="party-header">الفريق الأول (صاحب العمل / الشركة)</div>
        <div class="party-body">
            <span class="label">اسم الشركة:</span> <span class="val">{{ $first_party->company_name }}</span> <span class="separator">|</span>
            <span class="label">رقم السجل التجاري:</span> <span class="val">{{ $first_party->cr_number }}</span> <span class="separator">|</span>
            <span class="label">المقر:</span> <span class="val">{{ $first_party->hq }}</span> <span class="separator">|</span>
            <span class="label">يُمثلها بالتوقيع:</span> <span class="val">{{ $first_party->representative }}</span>
            <div style="margin-top: 8px; font-size: 12px; color: #64748b;">(ويُشار إليه في بنود هذا العقد بـ "الفريق الأول").</div>
        </div>
    </div>

    <div class="party-block">
        <div class="party-header">الفريق الثاني (مقدم الخدمة / السائق)</div>
        <div class="party-body">
            <span class="label">الاسم الكامل:</span> <span class="val">{{ $second_party->name }}</span> <span class="separator">|</span>
            <span class="label">اسم الأب:</span> <span class="val">{{ $second_party->father_name }}</span> <span class="separator">|</span>
            <span class="label">اسم الأم:</span> <span class="val">{{ $second_party->mother_name }}</span> <span class="separator">|</span>
            <span class="label">محل وتاريخ الولادة:</span> <span class="val">{{ $second_party->birth_place_date }}</span> <br>
            <span class="label">الرقم الوطني/رقم البطاقة:</span> <span class="val">{{ $second_party->national_id }}</span> <span class="separator">|</span>
            <span class="label">الأمانة:</span> <span class="val">{{ $second_party->amana }}</span> <span class="separator">|</span>
            <span class="label">القيد:</span> <span class="val">{{ $second_party->qaid }}</span> <br>
            <span class="label">العنوان الحالي:</span> <span class="val">{{ $second_party->address }}</span> <span class="separator">|</span>
            <span class="label">تاريخ منح البطاقة:</span> <span class="val">{{ $second_party->grant_date }}</span>
            <div style="margin-top: 8px; font-size: 12px; color: #64748b;">(ويُشار إليه في بنود هذا العقد بـ "الفريق الثاني").</div>
        </div>
    </div>

    <div class="terms-title">شروط وبنود التعاقد:</div>

    @if($terms->count() > 0)
        @foreach($terms as $index => $term)
            <div class="term-item">
                <span class="term-number">البند {{ $index + 1 }}:</span>
                {!! nl2br(e($term->term_text)) !!}
            </div>
        @endforeach
    @else
        <div class="term-item text-center" style="color: #94a3b8;">(لم يتم إضافة بنود للعقد بعد من قبل الإدارة)</div>
    @endif

    <br>

    <table class="signature-section">
        <tr>
            <td class="signature-td">
                <div class="sig-title">عن الفريق الأول (الشركة)</div>
                <div class="sig-line"><span class="label">الاسم والتوقيع:</span></div>
            </td>

            <td class="signature-td" style="background-color: #f8fafc;">
                <div class="sig-title">عن الفريق الثاني (السائق)</div>
                <div class="sig-line"><span class="label">الاسم والتوقيع:</span></div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
