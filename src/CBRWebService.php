<?php

namespace Tochka\CBRWebService;

/**
 * CBRWebService
 *
 * необходим
 * для взаимодействия с веб сервисом
 * Центробанка
 *
 * @author idbolshakov@gmail.com
 * @version 1.0.0
 */
class CBRWebService {
    
    const WSDL = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';

    private $soapClient;

    /**
     * конструктор
     *
     * создаем soap клиент для 
     * доступа к веб сервису Центробанка
     * на основе его WSDL
     */
    public function __construct() {

        $this->soapClient = new \SoapClient(self::WSDL);
    }

   /**
     * GetCursDynamicXML
     *
     * отвечает за получение
     * динамики ежедневных курсов валюты
     * на протяжении периода времени
     * от Центробанка
     * в формате XML
     *
     * @param $from {DateTime} - дата начала периода
     * @param $to   {DateTime} - дата окончания периода
     * @param $code {string}   - внутренний код валюты Центробанка 
     * @return {string} - xml c динамикой изменения курса 
     * 
     */
    public function getCursDynamicXML($from, $to, $code) {

        $params['FromDate']   = $from->format('Y-m-d');
        $params['ToDate']     = $to->format('Y-m-d');
        $params['ValutaCode'] = (string) $code;

        $response = $this->soapClient->GetCursDynamicXML($params);

        return $response->GetCursDynamicXMLResult->any;
    }
};
