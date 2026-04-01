<?php
/**
 * Calcula dias de atraso entre duas datas
 * 
 * @param string $dataVencimento Data de vencimento (Y-m-d)
 * @param string $dataPagamento Data do pagamento (Y-m-d), default = hoje
 * @param int|null $limiteDias Limite máximo de dias (ex: 30), null = sem limite
 * @return int Dias de atraso (mínimo 0)
 */
function calcularDiasAtraso($dataVencimento, $dataPagamento = null, $limiteDias = null) {
    if (empty($dataVencimento)) return 0;
    
    $dataPagamento = $dataPagamento ?? date('Y-m-d');
    $dataVenc = strtotime($dataVencimento);
    $dataPgto = strtotime($dataPagamento);
    
    if ($dataPgto <= $dataVenc) return 0; // Não está atrasado
    
    $dias = (int) floor(($dataPgto - $dataVenc) / 86400);
    
    if ($limiteDias !== null && $dias > $limiteDias) {
        return $limiteDias;
    }
    
    return $dias;
}
?>