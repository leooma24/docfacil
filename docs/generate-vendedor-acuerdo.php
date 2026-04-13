<?php
/**
 * Genera docs/acuerdo-vendedor.xlsx — Excel de referencia y tracking
 * para el vendedor, con los términos comerciales acordados.
 *
 * Contiene 4 hojas:
 *   1. Acuerdo      — términos comerciales en texto legible (para imprimir/firmar)
 *   2. Calculadora  — "si vendo X planes, gano Y". Editable.
 *   3. Tracking     — plantilla mes a mes donde el vendedor captura sus ventas
 *                     y el Excel calcula comisiones pendientes/pagadas/clawback
 *   4. Planes       — tabla de referencia con precios, comisiones totales y por mitad
 *
 * Uso: php docs/generate-vendedor-acuerdo.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

$sb = new Spreadsheet();
$sb->removeSheetByIndex(0);

// ============================================================
// HOJA 1 — ACUERDO (texto legible)
// ============================================================
$acuerdo = $sb->createSheet();
$acuerdo->setTitle('Acuerdo');

$acuerdo->getColumnDimension('A')->setWidth(4);
$acuerdo->getColumnDimension('B')->setWidth(100);

$row = 2;
$acuerdo->setCellValue("B{$row}", 'ACUERDO COMERCIAL — VENDEDOR DocFácil');
$acuerdo->getStyle("B{$row}")->getFont()->setBold(true)->setSize(18);
$acuerdo->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$acuerdo->getStyle("B{$row}")->getFont()->getColor()->setRGB('FFFFFF');
$acuerdo->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$acuerdo->getRowDimension($row)->setRowHeight(40);

$row += 2;
$sections = [
    ['1. Esquema de comisión', [
        'La comisión por cada nueva clínica vendida es de 3× la primera mensualidad del plan contratado.',
        'Ejemplo: plan Pro $299/mes → comisión total $897 por venta.',
        'Aplica a todos los planes de pago: Básico, Pro y Clínica. El plan Free NO paga comisión.',
    ]],
    ['2. Pago en dos exhibiciones (split 50/50)', [
        '50% (primera mitad) se paga cuando la clínica realiza su PRIMER pago real al sistema.',
        '50% (segunda mitad) se paga cuando la clínica realiza su SEGUNDO pago mensual.',
        'Ejemplo Pro: $448.50 al 1er pago + $448.50 al 2do pago = $897 total.',
        'Si la clínica NO hace el 2do pago, la segunda mitad no se paga.',
    ]],
    ['3. Clawback (devolución de comisión)', [
        'Si la clínica cancela antes de 90 días desde la fecha de venta, las comisiones quedan en estado "clawback".',
        'Las comisiones en clawback ya pagadas se descuentan del siguiente corte de pagos.',
        'Si la cancelación ocurre después de 90 días, NO hay clawback y el vendedor se queda con todo lo ganado.',
    ]],
    ['4. Atribución de la venta', [
        'Cada vendedor recibe un código único (ej. "VND-JUAN42").',
        'Link personalizado de registro: https://docfacil.tu-app.co/doctor/register?vnd=TU_CODIGO',
        'Cuando un doctor se registra con tu código, la clínica queda marcada como vendida por ti automáticamente.',
        'Una vez asignada, la atribución es inmutable: no se puede transferir a otro vendedor.',
    ]],
    ['5. Seguimiento y panel del vendedor', [
        'Acceso al panel en: https://docfacil.tu-app.co/ventas/login',
        'En tu panel puedes ver: tus prospectos, sus estados, tus comisiones pendientes y pagadas, el leaderboard.',
        'Solo ves TUS propios prospectos y comisiones. Nunca datos de otros vendedores ni de pacientes.',
    ]],
    ['6. Pagos al vendedor', [
        'Los pagos se realizan mensualmente a las comisiones en estado "Pagada" del mes.',
        'El admin revisa las comisiones pendientes al cierre del mes y las marca como pagadas al transferirte.',
        'Se descuentan automáticamente las comisiones en clawback del periodo.',
    ]],
    ['7. Terminación del acuerdo', [
        'Cualquiera de las partes puede terminar el acuerdo con 15 días de aviso.',
        'Las comisiones ya ganadas (tier "first" y "second" marcadas como pagadas) se respetan.',
        'Las comisiones pendientes de venta previas al aviso se pagan conforme al flujo normal.',
    ]],
];

foreach ($sections as [$title, $lines]) {
    $acuerdo->setCellValue("B{$row}", $title);
    $acuerdo->getStyle("B{$row}")->getFont()->setBold(true)->setSize(13);
    $acuerdo->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E1F5EE');
    $acuerdo->getRowDimension($row)->setRowHeight(25);
    $row++;

    foreach ($lines as $line) {
        $acuerdo->setCellValue("B{$row}", '  • ' . $line);
        $acuerdo->getStyle("B{$row}")->getAlignment()->setWrapText(true);
        $acuerdo->getRowDimension($row)->setRowHeight(22);
        $row++;
    }
    $row++;
}

$row++;
$acuerdo->setCellValue("B{$row}", 'Firma del vendedor: _____________________________     Fecha: _______________');
$acuerdo->getStyle("B{$row}")->getFont()->setItalic(true);
$row += 2;
$acuerdo->setCellValue("B{$row}", 'Firma DocFácil: _____________________________     Fecha: _______________');
$acuerdo->getStyle("B{$row}")->getFont()->setItalic(true);

// ============================================================
// HOJA 2 — CALCULADORA (escenarios "si vendo X, gano Y")
// ============================================================
$calc = $sb->createSheet();
$calc->setTitle('Calculadora');

$calc->setCellValue('A1', 'CALCULADORA DE INGRESOS — Juega con los números');
$calc->mergeCells('A1:F1');
$calc->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$calc->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$calc->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
$calc->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$calc->getRowDimension(1)->setRowHeight(30);

// Sección de inputs editables
$calc->setCellValue('A3', 'CAMBIA ESTOS VALORES PARA VER TUS INGRESOS:');
$calc->mergeCells('A3:F3');
$calc->getStyle('A3')->getFont()->setBold(true);
$calc->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF59D');

$calc->setCellValue('A4', 'Plan a vender');
$calc->setCellValue('A5', 'Ventas por mes (esperadas)');
$calc->setCellValue('A6', 'Retención (% que no cancelará <90 días)');

$calc->setCellValue('B4', 'Profesional');
$calc->setCellValue('B5', 10);
$calc->setCellValue('B6', 0.9);

$calc->getStyle('B4:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4');
$calc->getStyle('B4:B6')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
$calc->getStyle('B6')->getNumberFormat()->setFormatCode('0%');
$calc->getStyle('A4:A6')->getFont()->setBold(true);

// Data validation dropdown para el plan
$validation = $calc->getCell('B4')->getDataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setFormula1('"Básico,Pro,Clínica"');

// Valores derivados (tabla de lookup de precios)
$calc->setCellValue('D4', 'Precio mensual');
$calc->setCellValue('E4', '=IFERROR(VLOOKUP(B4,Planes!A4:B7,2,FALSE),0)');
$calc->getStyle('E4')->getNumberFormat()->setFormatCode('"$"#,##0.00');

$calc->setCellValue('D5', 'Comisión total por venta (3×)');
$calc->setCellValue('E5', '=IF(B4="Free",0,E4*3)');
$calc->getStyle('E5')->getNumberFormat()->setFormatCode('"$"#,##0.00');
$calc->getStyle('E5')->getFont()->setBold(true);

$calc->setCellValue('D6', 'Primera mitad (50%)');
$calc->setCellValue('E6', '=E5/2');
$calc->getStyle('E6')->getNumberFormat()->setFormatCode('"$"#,##0.00');

$calc->setCellValue('D7', 'Segunda mitad (50%)');
$calc->setCellValue('E7', '=E5/2');
$calc->getStyle('E7')->getNumberFormat()->setFormatCode('"$"#,##0.00');

$calc->getStyle('D4:D7')->getFont()->setBold(true);
$calc->getStyle('D4:E7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F5');

// Proyección mensual
$calc->setCellValue('A9', 'PROYECCIÓN DE INGRESOS');
$calc->mergeCells('A9:F9');
$calc->getStyle('A9')->getFont()->setBold(true)->setSize(13);
$calc->getStyle('A9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E1F5EE');
$calc->getRowDimension(9)->setRowHeight(25);

$headers = ['Periodo', 'Ventas', 'Comisión bruta', 'Efectiva (tras retención)', 'Acumulado'];
foreach ($headers as $i => $h) {
    $col = chr(65 + $i);
    $calc->setCellValue("{$col}10", $h);
}
$calc->getStyle('A10:E10')->getFont()->setBold(true);
$calc->getStyle('A10:E10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$calc->getStyle('A10:E10')->getFont()->getColor()->setRGB('FFFFFF');

$periods = [
    ['1 mes', 1],
    ['3 meses', 3],
    ['6 meses', 6],
    ['12 meses (1 año)', 12],
    ['24 meses (2 años)', 24],
];
$r = 11;
foreach ($periods as [$label, $months]) {
    $calc->setCellValue("A{$r}", $label);
    $calc->setCellValue("B{$r}", "=\$B\$5*{$months}");
    $calc->setCellValue("C{$r}", "=B{$r}*\$E\$5");
    $calc->setCellValue("D{$r}", "=C{$r}*\$B\$6");
    $calc->setCellValue("E{$r}", $r === 11 ? "=D{$r}" : "=D{$r}"); // cada fila es independiente, no acumulado real
    $calc->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
    $r++;
}
$calc->getStyle('A11:E' . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$calc->getStyle('D11:D' . ($r - 1))->getFont()->setBold(true)->getColor()->setRGB('0F6E56');

// Ejemplos memorables
$r += 2;
$calc->setCellValue("A{$r}", 'EJEMPLOS CONCRETOS');
$calc->mergeCells("A{$r}:F{$r}");
$calc->getStyle("A{$r}")->getFont()->setBold(true)->setSize(13);
$calc->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF59D');
$r += 2;

$examples = [
    'Si vendes 5 Básico al mes → 60 × $447 = $26,820/año',
    'Si vendes 1 Pro al mes → 12 × $898.50 = $10,782/año',
    'Si vendes 5 Pro al mes → 60 × $898.50 = $53,910/año',
    'Si vendes 10 Pro al mes → 120 × $898.50 = $107,820/año',
    'Si vendes 3 Pro + 1 Clínica al mes → (3×$898.50 + 1×$1,798.50) × 12 = $54,126/año',
    'Si vendes 5 Clínica al mes → 60 × $1,798.50 = $107,910/año 🚀',
    'Mix realista (5 Básico + 3 Pro + 1 Clínica/mes) → ($2,242.50 + $2,695.50 + $1,798.50) × 12 = $80,838/año',
    'Nota: solo el plan Free ($0) NO paga comisión. Todos los planes de pago califican.',
];
foreach ($examples as $ex) {
    $calc->setCellValue("A{$r}", '  ✓  ' . $ex);
    $calc->mergeCells("A{$r}:F{$r}");
    $calc->getRowDimension($r)->setRowHeight(20);
    $r++;
}

// Anchos de columna
$calc->getColumnDimension('A')->setWidth(30);
$calc->getColumnDimension('B')->setWidth(15);
$calc->getColumnDimension('C')->setWidth(18);
$calc->getColumnDimension('D')->setWidth(26);
$calc->getColumnDimension('E')->setWidth(18);

// ============================================================
// HOJA 3 — TRACKING (plantilla mes a mes)
// ============================================================
$tr = $sb->createSheet();
$tr->setTitle('Tracking');

$tr->setCellValue('A1', 'TRACKING DE VENTAS Y COMISIONES — Captura tus ventas aquí');
$tr->mergeCells('A1:J1');
$tr->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$tr->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$tr->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
$tr->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr->getRowDimension(1)->setRowHeight(30);

$tr->setCellValue('A2', 'Llena las columnas amarillas. El Excel calcula automáticamente las comisiones por cobrar, las pagadas y las totales.');
$tr->mergeCells('A2:J2');
$tr->getStyle('A2')->getFont()->setItalic(true);
$tr->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Encabezados
$headers = [
    'A' => '# ',
    'B' => 'Fecha venta',
    'C' => 'Clínica / Doctor',
    'D' => 'Plan',
    'E' => 'Comisión total',
    'F' => '1er pago recibido',
    'G' => '2do pago recibido',
    'H' => '¿Cancelada?',
    'I' => 'Días hasta cancelación',
    'J' => 'Estado comisión',
];
foreach ($headers as $col => $h) {
    $tr->setCellValue("{$col}4", $h);
}
$tr->getStyle('A4:J4')->getFont()->setBold(true);
$tr->getStyle('A4:J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$tr->getStyle('A4:J4')->getFont()->getColor()->setRGB('FFFFFF');
$tr->getStyle('A4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$tr->getRowDimension(4)->setRowHeight(30);

// Filas editables (30 filas)
$startRow = 5;
$endRow = 34;
for ($r = $startRow; $r <= $endRow; $r++) {
    $tr->setCellValue("A{$r}", $r - $startRow + 1);
    // Plan lookup para comisión
    $tr->setCellValue("E{$r}", "=IFERROR(IF(OR(D{$r}=\"Básico\",D{$r}=\"\"),0,VLOOKUP(D{$r},Planes!A4:C6,3,FALSE)),0)");

    // Días hasta cancelación
    $tr->setCellValue("I{$r}", "=IF(AND(H{$r}<>\"\",B{$r}<>\"\"),H{$r}-B{$r},\"\")");

    // Estado de la comisión (la lógica del observer en texto)
    $tr->setCellValue("J{$r}", sprintf(
        '=IF(C%1$d="","",IF(D%1$d="Básico","No califica",IF(AND(H%1$d<>"",I%1$d<90),"CLAWBACK",IF(AND(F%1$d<>"",G%1$d<>""),"Completa (100%%)",IF(F%1$d<>"","1ra mitad (50%%)","Pendiente")))))',
        $r
    ));
}

// Formato de fechas
$tr->getStyle("B{$startRow}:B{$endRow}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
$tr->getStyle("F{$startRow}:H{$endRow}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
$tr->getStyle("E{$startRow}:E{$endRow}")->getNumberFormat()->setFormatCode('"$"#,##0.00');

// Dropdown del plan
for ($r = $startRow; $r <= $endRow; $r++) {
    $validation = $tr->getCell("D{$r}")->getDataValidation();
    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $validation->setAllowBlank(true);
    $validation->setShowDropDown(true);
    $validation->setFormula1('"Básico,Profesional,Clínica"');
}

// Celdas editables en amarillo
$tr->getStyle("B{$startRow}:D{$endRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4');
$tr->getStyle("F{$startRow}:H{$endRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4');

// Bordes
$tr->getStyle("A4:J{$endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Formato condicional para la columna estado
$cfPaid = new Conditional();
$cfPaid->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
$cfPaid->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT);
$cfPaid->setText('Completa');
$cfPaid->getStyle()->getFont()->getColor()->setRGB('0F6E56');
$cfPaid->getStyle()->getFont()->setBold(true);

$cfClawback = new Conditional();
$cfClawback->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
$cfClawback->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT);
$cfClawback->setText('CLAWBACK');
$cfClawback->getStyle()->getFont()->getColor()->setRGB('C62828');
$cfClawback->getStyle()->getFont()->setBold(true);

$cfPending = new Conditional();
$cfPending->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
$cfPending->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT);
$cfPending->setText('Pendiente');
$cfPending->getStyle()->getFont()->getColor()->setRGB('E65100');

$tr->getStyle("J{$startRow}:J{$endRow}")->setConditionalStyles([$cfPaid, $cfClawback, $cfPending]);

// KPI resumen al final
$kpiRow = $endRow + 2;
$tr->setCellValue("A{$kpiRow}", 'RESUMEN');
$tr->mergeCells("A{$kpiRow}:J{$kpiRow}");
$tr->getStyle("A{$kpiRow}")->getFont()->setBold(true)->setSize(12);
$tr->getStyle("A{$kpiRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E1F5EE');

$kpiRow++;
$tr->setCellValue("A{$kpiRow}", 'Ventas capturadas:');
$tr->setCellValue("E{$kpiRow}", "=COUNTA(C{$startRow}:C{$endRow})");
$tr->getStyle("A{$kpiRow}")->getFont()->setBold(true);

$kpiRow++;
$tr->setCellValue("A{$kpiRow}", 'Comisión generada (sin clawback):');
$tr->setCellValue("E{$kpiRow}", "=SUMIFS(E{$startRow}:E{$endRow},J{$startRow}:J{$endRow},\"Completa*\")+SUMIFS(E{$startRow}:E{$endRow},J{$startRow}:J{$endRow},\"1ra mitad*\")/2");
$tr->getStyle("E{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
$tr->getStyle("A{$kpiRow}")->getFont()->setBold(true);
$tr->getStyle("E{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('0F6E56');

$kpiRow++;
$tr->setCellValue("A{$kpiRow}", 'Comisión pendiente de cobrar:');
$tr->setCellValue("E{$kpiRow}", "=SUMIFS(E{$startRow}:E{$endRow},J{$startRow}:J{$endRow},\"Pendiente*\")");
$tr->getStyle("E{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
$tr->getStyle("A{$kpiRow}")->getFont()->setBold(true);
$tr->getStyle("E{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('E65100');

$kpiRow++;
$tr->setCellValue("A{$kpiRow}", 'Comisión en clawback (a descontar):');
$tr->setCellValue("E{$kpiRow}", "=SUMIFS(E{$startRow}:E{$endRow},J{$startRow}:J{$endRow},\"CLAWBACK\")");
$tr->getStyle("E{$kpiRow}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
$tr->getStyle("A{$kpiRow}")->getFont()->setBold(true);
$tr->getStyle("E{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('C62828');

// Anchos
$tr->getColumnDimension('A')->setWidth(5);
$tr->getColumnDimension('B')->setWidth(13);
$tr->getColumnDimension('C')->setWidth(28);
$tr->getColumnDimension('D')->setWidth(14);
$tr->getColumnDimension('E')->setWidth(16);
$tr->getColumnDimension('F')->setWidth(17);
$tr->getColumnDimension('G')->setWidth(17);
$tr->getColumnDimension('H')->setWidth(14);
$tr->getColumnDimension('I')->setWidth(12);
$tr->getColumnDimension('J')->setWidth(20);

// ============================================================
// HOJA 4 — PLANES (tabla de lookup)
// ============================================================
$pl = $sb->createSheet();
$pl->setTitle('Planes');

$pl->setCellValue('A1', 'TABLA DE PLANES — Usada por las fórmulas');
$pl->mergeCells('A1:E1');
$pl->getStyle('A1')->getFont()->setBold(true);
$pl->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F6E56');
$pl->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

$pl->setCellValue('A3', 'Plan');
$pl->setCellValue('B3', 'Precio mensual');
$pl->setCellValue('C3', 'Comisión total (3×)');
$pl->setCellValue('D3', 'Por mitad (50%)');
$pl->setCellValue('E3', '¿Califica?');
$pl->getStyle('A3:E3')->getFont()->setBold(true);
$pl->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E1F5EE');

$planes = [
    ['Free', 0, 0, 0, 'NO'],
    ['Básico', 149, 447, 223.50, 'SÍ'],
    ['Pro', 299, 897, 448.50, 'SÍ'],
    ['Clínica', 499, 1497, 748.50, 'SÍ'],
];
$r = 4;
foreach ($planes as $p) {
    $pl->setCellValue("A{$r}", $p[0]);
    $pl->setCellValue("B{$r}", $p[1]);
    $pl->setCellValue("C{$r}", $p[2]);
    $pl->setCellValue("D{$r}", $p[3]);
    $pl->setCellValue("E{$r}", $p[4]);
    $r++;
}
$pl->getStyle('B4:D6')->getNumberFormat()->setFormatCode('"$"#,##0.00');
$pl->getStyle('A3:E6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$pl->getStyle('E4:E6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$pl->getColumnDimension('A')->setWidth(16);
$pl->getColumnDimension('B')->setWidth(18);
$pl->getColumnDimension('C')->setWidth(22);
$pl->getColumnDimension('D')->setWidth(18);
$pl->getColumnDimension('E')->setWidth(14);

// Notas
$pl->setCellValue('A9', 'NOTA: Todos los planes de pago (Básico, Pro, Clínica) pagan comisión. Solo el plan Free NO paga.');
$pl->mergeCells('A9:E9');
$pl->getStyle('A9')->getFont()->setItalic(true);
$pl->setCellValue('A10', 'La comisión es 3× la mensualidad, dividida en 2 pagos (al 1er y al 2do pago del cliente).');
$pl->mergeCells('A10:E10');
$pl->getStyle('A10')->getFont()->setItalic(true);

// ============================================================
// Activar primera hoja y guardar
// ============================================================
$sb->setActiveSheetIndexByName('Acuerdo');

$outputPath = __DIR__ . '/acuerdo-vendedor.xlsx';
$writer = new Xlsx($sb);
$writer->save($outputPath);

echo "✓ Excel generado: {$outputPath}\n";
echo "  Hojas: Acuerdo, Calculadora, Tracking, Planes\n";
