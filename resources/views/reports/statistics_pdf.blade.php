<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'cairo', sans-serif; color: #333; font-size: 13px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .header h1 { color: #1e3a8a; margin: 0; font-size: 24px; }
        .header p { color: #666; margin: 5px 0 0 0; }

        .cards-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .card-cell { width: 25%; padding: 8px; }
        .card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
        .card-title { font-size: 11px; color: #64748b; margin-bottom: 5px; }
        .card-value { font-size: 16px; font-weight: bold; color: #0f172a; }

        .section-title { font-size: 15px; color: #1e3a8a; margin-top: 20px; margin-bottom: 10px; border-right: 3px solid #3b82f6; padding-right: 8px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background-color: #f1f5f9; color: #1e293b; text-align: right; padding: 10px; border: 1px solid #cbd5e1; font-weight: bold; }
        table.data-table td { padding: 9px; border: 1px solid #cbd5e1; text-align: right; }
        table.data-table tr:nth-child(even) { background-color: #f8fafc; }

        .badge { background-color: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-size: 11px; }
        .text-center { text-align: center !important; }
        .text-danger { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>تقرير الإحصائيات المالي واللوجستي الشامل</h1>
        <p>نظام TRANSNET لإدارة الشحن | تاريخ الاستخراج: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table class="cards-table">
        <tr>
            <td class="card-cell">
                <div class="card">
                    <div class="card-title">شحنات اليوم</div>
                    <div class="card-value">{{ $today_shipments }}</div>
                </div>
            </td>
            <td class="card-cell">
                <div class="card">
                    <div class="card-title">أرباح الشهر الحالي (15%)</div>
                    <div class="card-value">{{ number_format($this_month_earnings, 0) }} ل.س</div>
                </div>
            </td>
            <td class="card-cell">
                <div class="card">
                    <div class="card-title">إجمالي العملاء المسجلين</div>
                    <div class="card-value">{{ $total_clients }}</div>
                </div>
            </td>
            <td class="card-cell">
                <div class="card">
                    <div class="card-title">إجمالي السائقين المسجلين</div>
                    <div class="card-value">{{ $total_drivers }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">أولاً: كثافة الشحنات حسب المحافظات والمناطق</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%" class="text-center">رقم المحافظة</th>
                <th width="50%">اسم المحافظة</th>
                <th width="35%" class="text-center">عدد الشحنات الصادرة منها</th>
            </tr>
        </thead>
        <tbody>
            @forelse($governorate_stats as $gov)
                <tr>
                    <td class="text-center">{{ $gov->id }}</td>
                    <td>{{ $gov->name }}</td>
                    <td class="text-center font-weight-bold">{{ $gov->shipments_count }} شحنة</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">لا توجد بيانات متاحة للمحافظات في النطاق المحدد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">ثانياً: التقرير المالي الصافي للأرباح الدورية</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>الفترة الزمنية</th>
                <th class="text-center">صافي الأرباح المحققة المقدرة (15%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($earnings_stats['earnings_by_date'] as $date => $amount)
                <tr>
                    <td style="font-family: monospace;">{{ $date }}</td>
                    <td class="text-center" style="color: #16a34a; font-weight: bold;">{{ number_format($amount, 0) }} ل.س</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">لا توجد حركة مالية مرصودة ضمن الفترة المحددة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-top: -10px; margin-bottom: 20px;">
        <tr>
            <td style="background-color: #fef2f2; border: 1px solid #fee2e2; padding: 12px; border-radius: 4px;">
                ⚠️ <strong>مستحقات معلقة بانتظار التحصيل:</strong>
                يوجد حالياً <span class="text-danger">{{ $earnings_stats['unpaid_shipments_count'] }}</span> شحنة غير مدفوعة
                وتقدر أرباحها المعلقة بـ <span class="text-danger">{{ number_format($earnings_stats['unpaid_shipments_earnings'], 0) }} ل.س</span>.
            </td>
        </tr>
    </table>

    <div class="section-title">ثالثاً: البنية الرقمية وحالة حسابات المستخدمين</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>تصنيف الحساب (الدور والمشروع)</th>
                <th class="text-center">العدد الإجمالي</th>
                <th class="text-center">النسبة المئوية من الحسابات الفعالة</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>العملاء النشطون</td>
                <td class="text-center">{{ $user_stats['clients_count'] }}</td>
                <td class="text-center">{{ $user_stats['clients_percentage'] }} %</td>
            </tr>
            <tr>
                <td>السائقون النشطون والمتاحون</td>
                <td class="text-center">{{ $user_stats['drivers_count'] }}</td>
                <td class="text-center">{{ $user_stats['drivers_percentage'] }} %</td>
            </tr>
            <tr>
                <td>حسابات السائقين المجمدة مؤقتاً</td>
                <td class="text-center">{{ $user_stats['frozen_drivers_count'] }}</td>
                <td class="text-center">{{ $user_stats['frozen_drivers_percentage'] }} %</td>
            </tr>
            <tr>
                <td>العملاء المحظورون</td>
                <td class="text-center text-danger">{{ $user_stats['blocked_clients_count'] }}</td>
                <td class="text-center">{{ $user_stats['blocked_clients_percentage'] }} %</td>
            </tr>
            <tr>
                <td>السائقون المحظورون</td>
                <td class="text-center text-danger">{{ $user_stats['blocked_drivers_count'] }}</td>
                <td class="text-center">{{ $user_stats['blocked_drivers_percentage'] }} %</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
