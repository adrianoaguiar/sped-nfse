<?php

namespace NFePHP\NFSe\Models\Prodam\Factories;

/**
 * Classe para a construção do XML relativo ao serviço de
 * Pedido de Consulta de NFSe para os webservices da
 * Cidade de São Paulo conforme o modelo Prodam
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Models\Prodam\Factories\ConsultaNFSe
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use InvalidArgumentException;
use NFePHP\NFSe\Models\Prodam\Factories\Header;
use NFePHP\NFSe\Models\Prodam\Factories\Factory;

class ConsultaNFSe extends Factory
{
    /**
     *
     * @param type $versao
     * @param type $remetenteTipoDoc
     * @param type $remetenteCNPJCPF
     * @param type $transacao
     * @param type $chavesNFSe
     * @param type $chavesRPS
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $transacao = '',
        $chavesNFSe = [],
        $chavesRPS = []
    ) {
        $method = "PedidoConsultaNFe";
        $content = "<$method "
            . "xmlns:xsd=\""
            . $this->xmlnsxsd
            . "\" xmlns=\""
            . $this->xmlns
            . "\" xmlns:xsi=\""
            . $this->xmlnsxsi
            . "\">";
        $content .= Header::render($versao, $remetenteTipoDoc, $remetenteCNPJCPF, $transacao);
        //minimo 1 e maximo de 50 objetos podem ser consultados
        $total = count($chavesNFSe) + count($chavesRPS);
        if ($total == 0 || $total > 50) {
            $msg = "Na consulta deve haver pelo menos uma chave e no máximo 50. Fornecido: $total";
            throw new InvalidArgumentException($msg);
        }
        //para cada chave montar um detalhe
        foreach ($chavesNFSe as $chave) {
            $content .= "<Detalhe xmlns=\"\">";
            $content .= "<ChaveNFe>";
            $content .= "<InscricaoPrestador>".$chave['prestadorIM']."</InscricaoPrestador>";
            $content .= "<NumeroNFe>".$chave['numeroNFSe']."</NumeroNFe>";
            $content .= "</ChaveNFe>";
            $content .= "</Detalhe>";
        }
        foreach ($chavesRPS as $chave) {
            $content .= "<Detalhe xmlns=\"\">";
            $content .= "<ChaveRPS>";
            $content .= "<InscricaoPrestador>".$chave['prestadorIM']."</InscricaoPrestador>";
            $content .= "<SerieRPS>".$chave['serieRPS']."</SerieRPS>";
            $content .= "<NumeroRPS>".$chave['numeroRPS']."</NumeroRPS>";
            $content .= "</ChaveRPS>";
            $content .= "</Detalhe>";
        }
        $content .= "</$method>";
        $body = $this->oCertificate->signXML($content, $method, '', $algorithm = 'SHA1');
        $body = $this->clear($body);
        $this->validar($versao, $body, $method);
        return $body;
    }
}
