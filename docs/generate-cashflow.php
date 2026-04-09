<?php
/**
 * Generador del Excel de cashflow para el módulo de ventas de DocFácil.
 *
 * Crea docs/sales-cashflow.xlsx con 4 hojas:
 *   - Resumen: comparativo de los 3 planes
 *   - Básico, Profesional, Clínica: corridas mes-a-mes con FÓRMULAS EDITABLES
 *
 * Las hojas por plan tienen celdas de parámetros arriba (vendedores, ventas/mes,
 * churn, precio, comisión multiplicador, costo op). Cambiar cualquiera de esos
 * valores recalcula toda la tabla automáticamente en Excel.
 *
 * Uso: php docs/generate-cashflow.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Conditional\ConditionalFormatting\Wizard\CellValue;

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // remove default

// ============================================================
// Helper: crear una hoja de corrida por plan
// ============================================================
function createPlanSheet(Spreadsheet $sb, string $sheetName, array $params): void
{
    $sheet = $sb->createSheet();
    $sheet->setTitle($sheetName);

    // ==== PARÁMETROS EDITABLES (zona amarilla) ====
    $sheet->setCellValue('A1', 'PARÁMETROS EDITABLES — cambia los valores y toda la tabla se recalcula');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF59D');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $rows = [
        ['A3', 'Plan',                        'B3', $params['plan_name']],
        ['A4', 'Precio mensual (MXN)',        'B4', $params['price']],
        ['A5', '# Vendedores',                'B5', $params['reps']],
        ['A6', 'Ventas/mes por vendedor',     'B6', $params['sales_per_rep']],
        ['A7', 'Multiplicador comisión',      'B7', $params['multiplier']],
        ['A8', 'Split primera mitad (%)',     'B8', 0.5],
        ['A9', 'Churn mensual (%)',           'B9', $params['churn']],
        ['A10', 'Costo operativo/cliente (MXN)', 'B10', $params['op_cost']],
    ];
    foreach ($rows as [$labelCell, $label, $valueCell, $value]) {
        $sheet->setCellValue($labelCell, $label);
        $sheet->setCellValue($valueCell, $value);
        $sheet->getStyle($labelCell)->getFont()->setBold(true);
        $sheet->getStyle($valueCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4');
        $sheet->getStyle($valueCell)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }

    // Formatos
    $sheet->getStyle('B4')->getNumberFormat()->setFormatCode('"$"#,##0.00');
    $sheet->getStyle('B9')->getNumberFormat()->setFormatCode('0.0%');
    $sheet->getStyle('B8')->getNumberFormat()->setFormatCode('0%');
    $sheet->getStyle('B10')->getNumberFormat()->setFormatCode('"$"#,##0.00');

    // ==== CELDAS DERIVADAS ====
    $sheet->setCellValue('D3', 'Valores derivados');
    $sheet->getStyle('D3')->getFont()->setBold(true);
    $sheet->setCellValue('D4', 'Nuevos/mes');
    $sheet->setCellValue('E4', '=B5*B6');
    $sheet->setCellValue('D5', 'Comisión total/venta');
    $sheet->setCellValue('E5', '=B4*B7');
    $sheet->getStyle('E5')->getNumberFormat()->setFormatCode('"$"#,##0.00');
    $sheet->setCellValue('D6', 'Primera mitad/venta');
    $sheet->setCellValue('E6', '=E5*B8');
    $sheet->getStyle('E6')->getNumberFormat()->setFormatCode('"$"#,##0.00');
    $sheet->setCellValue('D7', 'Segunda mitad/venta');
    $sheet->setCellValue('E7', '=E5*(1-B8)');
    $sheet->getStyle('E7')->getNumberFormat()->setFormatCode('"$"#,##0.00');

    // ==== HEADER TABLA ====
    $headerRow = 13;
    $headers = ['Mes', 'Activos inicio', 'Nuevos', 'Activos después churn', 'Ingreso MRR', 'Costo operativo', 'Comisión mes 1ra', 'Comisión mes 2da', 'Comisión total', 'Neto mes', 'Acumulado'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . $headerRow, $h);
        $col++;
    }
    $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getFont()->setBold(true);
    $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
    $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // ==== FILAS DE LA TABLA (12 meses) ====
    // Fórmulas:
    // A = mes number
    // B (activos inicio) = primera fila = 0; siguientes = D anterior
    // C (nuevos) = $E$4
    // D (activos después churn) = (B + C) * (1 - $B$9)
    // E (ingreso) = D * $B$4
    // F (costo op) = -D * $B$10
    // G (comisión 1ra mitad) = -C * $E$6 (por los nuevos del mes)
    // H (comisión 2da mitad) = -(C del mes anterior) * $E$7 ; en mes 1 = 0
    // I (comisión total) = G + H
    // J (neto mes) = E + F + I
    // K (acumulado) = K anterior + J ; en mes 1 = J
    for ($m = 1; $m <= 12; $m++) {
        $r = $headerRow + $m;
        $prevR = $r - 1;

        $sheet->setCellValue("A{$r}", $m);

        // B: activos inicio
        if ($m === 1) {
            $sheet->setCellValue("B{$r}", 0);
        } else {
            $sheet->setCellValue("B{$r}", "=D{$prevR}");
        }

        // C: nuevos
        $sheet->setCellValue("C{$r}", "=\$E\$4");

        // D: activos después churn = (B + C) * (1 - churn)
        $sheet->setCellValue("D{$r}", "=(B{$r}+C{$r})*(1-\$B\$9)");

        // E: ingreso
        $sheet->setCellValue("E{$r}", "=D{$r}*\$B\$4");

        // F: costo op (negativo)
        $sheet->setCellValue("F{$r}", "=-D{$r}*\$B\$10");

        // G: comisión 1ra mitad (por los nuevos del mes, negativo)
        $sheet->setCellValue("G{$r}", "=-C{$r}*\$E\$6");

        // H: comisión 2da mitad (por los nuevos del mes anterior)
        if ($m === 1) {
            $sheet->setCellValue("H{$r}", 0);
        } else {
            $sheet->setCellValue("H{$r}", "=-C{$prevR}*\$E\$7");
        }

        // I: comisión total
        $sheet->setCellValue("I{$r}", "=G{$r}+H{$r}");

        // J: neto mes
        $sheet->setCellValue("J{$r}", "=E{$r}+F{$r}+I{$r}");

        // K: acumulado
        if ($m === 1) {
            $sheet->setCellValue("K{$r}", "=J{$r}");
        } else {
            $sheet->setCellValue("K{$r}", "=K{$prevR}+J{$r}");
        }
    }

    // Formato de dólares para columnas numéricas
    $lastRow = $headerRow + 12;
    $sheet->getStyle("E" . ($headerRow + 1) . ":K{$lastRow}")
        ->getNumberFormat()->setFormatCode('"$"#,##0;[Red]"-$"#,##0');
    $sheet->getStyle("B" . ($headerRow + 1) . ":D{$lastRow}")
        ->getNumberFormat()->setFormatCode('#,##0.0');

    // Bordes
    $sheet->getStyle("A{$headerRow}:K{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Formato condicional: acumulado rojo si negativo, verde si positivo
    $conditionalStyles = [];

    $conditionalRed = new Conditional();
    $conditionalRed->setConditionType(Conditional::CONDITION_CELLIS);
    $conditionalRed->setOperatorType(Conditional::OPERATOR_LESSTHAN);
    $conditionalRed->addCondition(0);
    $conditionalRed->getStyle()->getFont()->getColor()->setRGB('C62828');
    $conditionalRed->getStyle()->getFont()->setBold(true);
    $conditionalStyles[] = $conditionalRed;

    $conditionalGreen = new Conditional();
    $conditionalGreen->setConditionType(Conditional::CONDITION_CELLIS);
    $conditionalGreen->setOperatorType(Conditional::OPERATOR_GREATERTHANOREQUAL);
    $conditionalGreen->addCondition(0);
    $conditionalGreen->getStyle()->getFont()->getColor()->setRGB('0F6E56');
    $conditionalGreen->getStyle()->getFont()->setBold(true);
    $conditionalStyles[] = $conditionalGreen;

    $sheet->getStyle("K" . ($headerRow + 1) . ":K{$lastRow}")->setConditionalStyles($conditionalStyles);

    // ==== SECCIÓN DE INDICADORES CLAVE ====
    $kpiRow = $lastRow + 2;
    $sheet->setCellValue("A{$kpiRow}", 'INDICADORES CLAVE');
    $sheet->mergeCells("A{$kpiRow}:K{$kpiRow}");
    $sheet->getStyle("A{$kpiRow}")->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle("A{$kpiRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E1F5EE');
    $sheet->getStyle("A{$kpiRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $kpiRow++;
    $sheet->setCellValue("A{$kpiRow}", 'Caja mínima requerida (el valor más negativo del acumulado)');
    $sheet->setCellValue("F{$kpiRow}", "=ABS(MIN(K" . ($headerRow + 1) . ":K{$lastRow}))");
    $sheet->getStyle("F{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0');
    $sheet->getStyle("F{$kpiRow}")->getFont()->setBold(true);

    $kpiRow++;
    $sheet->setCellValue("A{$kpiRow}", 'Caja recomendada (mínima + 50% colchón)');
    $sheet->setCellValue("F{$kpiRow}", "=F" . ($kpiRow - 1) . "*1.5");
    $sheet->getStyle("F{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0');
    $sheet->getStyle("F{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('0F6E56');

    $kpiRow++;
    $sheet->setCellValue("A{$kpiRow}", 'Ingreso mensual mes 12 (MRR estimado)');
    $sheet->setCellValue("F{$kpiRow}", "=E{$lastRow}");
    $sheet->getStyle("F{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0');
    $sheet->getStyle("F{$kpiRow}")->getFont()->setBold(true);

    $kpiRow++;
    $sheet->setCellValue("A{$kpiRow}", 'Margen neto mensual mes 12');
    $sheet->setCellValue("F{$kpiRow}", "=J{$lastRow}");
    $sheet->getStyle("F{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0');
    $sheet->getStyle("F{$kpiRow}")->getFont()->setBold(true);

    // Anchos de columna
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(12);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(16);
    $sheet->getColumnDimension('F')->setWidth(16);
    $sheet->getColumnDimension('G')->setWidth(18);
    $sheet->getColumnDimension('H')->setWidth(18);
    $sheet->getColumnDimension('I')->setWidth(16);
    $sheet->getColumnDimension('J')->setWidth(14);
    $sheet->getColumnDimension('K')->setWidth(16);
}

// ============================================================
// HOJA RESUMEN
// ============================================================
$summary = $spreadsheet->createSheet(0);
$summary->setTitle('Resumen');

$summary->setCellValue('A1', 'DocFácil — Corridas de caja del módulo de ventas');
$summary->mergeCells('A1:F1');
$summary->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$summary->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$summary->setCellValue('A2', 'Parámetros: 3 vendedores × 10 ventas/mes · comisión 3× split 50/50 · churn 2.5% mensual');
$summary->mergeCells('A2:F2');
$summary->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$summary->getStyle('A2')->getFont()->setItalic(true);

// Encabezado
$summary->setCellValue('A4', 'Plan');
$summary->setCellValue('B4', 'Precio mensual');
$summary->setCellValue('C4', 'Caja mínima');
$summary->setCellValue('D4', 'Caja recomendada (+50% colchón)');
$summary->setCellValue('E4', 'Breakeven');
$summary->setCellValue('F4', 'Margen mensual mes 12');
$summary->getStyle('A4:F4')->getFont()->setBold(true);
$summary->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$summary->getStyle('A4:F4')->getFont()->getColor()->setRGB('FFFFFF');
$summary->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Referencias cruzadas a las hojas de cada plan
// La caja mínima en cada hoja está en F{kpiRow} — como sabemos que es lastRow+2+1 = 13+12+3 = row 28
$cajaMinRow = 28; // tabla termina row 25, KPI header en row 27, caja mínima en row 28
$cajaRecRow = 29;
$margenMes12Row = 31;

$summary->setCellValue('A5', 'Básico');
$summary->setCellValue('B5', '=Básico!B4');
$summary->setCellValue("C5", "=Básico!F{$cajaMinRow}");
$summary->setCellValue("D5", "=Básico!F{$cajaRecRow}");
$summary->setCellValue('E5', 'Mes 6');
$summary->setCellValue("F5", "=Básico!F{$margenMes12Row}");

$summary->setCellValue('A6', 'Profesional');
$summary->setCellValue('B6', '=Profesional!B4');
$summary->setCellValue("C6", "=Profesional!F{$cajaMinRow}");
$summary->setCellValue("D6", "=Profesional!F{$cajaRecRow}");
$summary->setCellValue('E6', 'Mes 6');
$summary->setCellValue("F6", "=Profesional!F{$margenMes12Row}");

$summary->setCellValue('A7', 'Clínica');
$summary->setCellValue('B7', '=Clínica!B4');
$summary->setCellValue("C7", "=Clínica!F{$cajaMinRow}");
$summary->setCellValue("D7", "=Clínica!F{$cajaRecRow}");
$summary->setCellValue('E7', 'Mes 6');
$summary->setCellValue("F7", "=Clínica!F{$margenMes12Row}");

$summary->getStyle('B5:D7')->getNumberFormat()->setFormatCode('"$"#,##0');
$summary->getStyle('F5:F7')->getNumberFormat()->setFormatCode('"$"#,##0');
$summary->getStyle('A4:F7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$summary->getStyle('D5:D7')->getFont()->setBold(true)->getColor()->setRGB('0F6E56');
$summary->getStyle('E5:E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Notas
$summary->setCellValue('A9', 'NOTAS IMPORTANTES');
$summary->getStyle('A9')->getFont()->setBold(true)->setSize(12);
$summary->getStyle('A9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF59D');

$notes = [
    '1. Supuestos: sin rampa de vendedores (cierran 10/mes desde el mes 1). En realidad es menos el primer mes.',
    '2. Costo operativo: Básico $20/cliente/mes, Profesional $30, Clínica $50 (incluye hosting, WhatsApp, email, Stripe fees).',
    '3. Mes 3 es siempre el peor momento: pagas la segunda mitad del cohorte 2 + la primera del cohorte 3.',
    '4. Breakeven cae en mes 6 en los 3 escenarios por la estructura 50/50.',
    '5. Si el vendedor cierra solo 5/mes en lugar de 10, multiplica todo por 0.5 (caja mínima y breakeven).',
    '6. Las comisiones se pagan SOLO si el cliente hace su primer pago (50%) y su segundo pago (50%). Si cancela antes de 90 días, clawback.',
    '7. Cada hoja tiene parámetros editables arriba. Cambia # vendedores, churn, precio, ventas/mes y todo se recalcula.',
    '8. MRR = Monthly Recurring Revenue = ingreso recurrente mensual por clientes activos.',
];
$r = 10;
foreach ($notes as $note) {
    $summary->setCellValue("A{$r}", $note);
    $summary->mergeCells("A{$r}:F{$r}");
    $r++;
}

$summary->getColumnDimension('A')->setWidth(18);
$summary->getColumnDimension('B')->setWidth(18);
$summary->getColumnDimension('C')->setWidth(18);
$summary->getColumnDimension('D')->setWidth(28);
$summary->getColumnDimension('E')->setWidth(14);
$summary->getColumnDimension('F')->setWidth(22);

// ============================================================
// HOJAS POR PLAN
// ============================================================
createPlanSheet($spreadsheet, 'Básico', [
    'plan_name'   => 'Básico',
    'price'       => 149,
    'reps'        => 3,
    'sales_per_rep' => 10,
    'multiplier'  => 3,
    'churn'       => 0.025,
    'op_cost'     => 20,
]);

createPlanSheet($spreadsheet, 'Profesional', [
    'plan_name'   => 'Profesional',
    'price'       => 299,
    'reps'        => 3,
    'sales_per_rep' => 10,
    'multiplier'  => 3,
    'churn'       => 0.025,
    'op_cost'     => 30,
]);

createPlanSheet($spreadsheet, 'Clínica', [
    'plan_name'   => 'Clínica',
    'price'       => 599,
    'reps'        => 3,
    'sales_per_rep' => 10,
    'multiplier'  => 3,
    'churn'       => 0.025,
    'op_cost'     => 50,
]);

// Hoja activa al abrir = Resumen
$spreadsheet->setActiveSheetIndexByName('Resumen');

// ============================================================
// GUARDAR
// ============================================================
$outputPath = __DIR__ . '/sales-cashflow.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputPath);

echo "✓ Excel generado: {$outputPath}\n";
echo "  Hojas: Resumen, Básico, Profesional, Clínica\n";
echo "  Las fórmulas se recalculan automáticamente al cambiar parámetros.\n";
