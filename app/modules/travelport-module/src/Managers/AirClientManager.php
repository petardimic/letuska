<?php
/**
 * This file is part of the superletuska.cz
 * Copyright (c) 2015
 *
 * @file    AirClientManager.php
 * @author  Pavel Paulík <pavel.paulik1@gmail.com>
 */

namespace TravelPortModule\Managers;


use Tracy\Debugger;
use TravelPortModule\Air\AvailabilitySearchReq;
use TravelPortModule\Air\AvailabilitySearchRsp;
use TravelPortModule\Air\LowFareSearchReq;
use TravelPortModule\Air\LowFareSearchRsp;
use TravelPortModule\InvalidArgumentException;
use TravelPortModule\TravelPortSoapClient;

class AirClientManager extends TravelPortSoapClient
{
    const SERVICE_NS = 'air';
    const SERVICE_NAME = 'AirService';

    const KEY_LOWFARE_SEARCH_RSP = 'LowFareSearchRsp';

    const KEY_FAULT = 'Fault';
    const KEY_FAULT_CODE = 'faultcode';
    const KEY_FAULT_STRING = 'faultstring';
    const KEY_FAULT_CODE_DATA = 'Server.Data';


    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }


    public function airRepriceReq(AirRepriceReq $airRepriceReq)
    {


        $airRepriceRsp = new AirRepriceRsp;
        return $airRepriceRsp;
    }


    public function scheduleSearchReq(ScheduleSearchReq $scheduleSearchReq)
    {


        $scheduleSearchReq = new ScheduleSearchReq;
        return $scheduleSearchReq;
    }


    /**
     * @param LowFareSearchReq $lowFareSearchReq
     *
     * @return LowFareSearchRsp
     */
    public function lowFareSearchReq(LowFareSearchReq $lowFareSearchReq)
    {
        $this->setUseCache(true);
        $this->__soapCall('service', array($lowFareSearchReq), array(self::CUSTOM_GENERATE => true));

        $responseO = new LowFareSearchRsp();

        $responseArray = $this->responseToArray($this->getLastResponse())['Body'];

        if (isset($responseArray[self::KEY_FAULT])) {
            if ($responseArray[self::KEY_FAULT][self::KEY_FAULT_CODE] == 'Server.Data' ||
                $responseArray[self::KEY_FAULT][self::KEY_FAULT_CODE] == 'Server.ValidationException' ||
                $responseArray[self::KEY_FAULT][self::KEY_FAULT_CODE] == 'Server.Business'
            ) {
                throw new InvalidArgumentException($responseArray[self::KEY_FAULT][self::KEY_FAULT_STRING]);

            } else {
                throw new InvalidArgumentException($responseArray[self::KEY_FAULT][self::KEY_FAULT_STRING]);
            }
        }

        $responseA = $this->responseToArray($this->getLastResponse())['Body'][self::KEY_LOWFARE_SEARCH_RSP];
        $this->createResponse($responseO, $responseA);
        return $responseO;
    }


    public function lowFareSearchAsynchReq(LowFareSearchAsynchReq $lowFareSearchAsynchReq)
    {


        $lowFareSearchAsynchReq = new LowFareSearchAsynchReq;
        return $lowFareSearchAsynchReq;
    }


    public function retrieveLowFareSearchReq(RetrieveLowFareSearchReq $retrieveLowFareSearchReq)
    {


        $retrieveLowFareSearchReq = new RetrieveLowFareSearchReq;
        return $retrieveLowFareSearchReq;
    }


    public function AirPriceReq(AirPriceReq $airPriceReq)
    {


        $airPriceRsp = new AirPriceRsp;
        return $airPriceRsp;
    }


    public function AirFareRulesReq(AirFareRulesReq $airFareRulesReq)
    {


        $airFareRulesRsp = new AirFareRulesRsp;
        return $airFareRulesRsp;
    }


    public function AvailabilitySearchReq(AvailabilitySearchReq $availabilitySearchReq)
    {
        $this->createCustomRequest($availabilitySearchReq);

        $this->sendFromCurl();


        $this->__soapCall('service', array($availabilitySearchReq), array(self::CUSTOM_GENERATE => true));
        dump($this->getLastResponse());

        $responseO = new AvailabilitySearchRsp();
        dump($responseO);


        $responseA = $this->responseToArray($this->getLastResponse())['Body']['LowFareSearchRsp'];

        $this->createResponse($responseO, $responseA);

        dump($responseA);
        dump($responseO);

//        $test = new \AirStructLowFareSearchReq();
//        die(dump($test));


//        $this->service($test);
//        $this->service($lowFareSearchReq);
//        $this->service(array($lowFareSearchReq));
//        $this->service(array($lowFareSearchReq));

        die(dump($this->getDomDocument()->saveXML()));


        $availabilitySearchRsp = new AvailabilitySearchRsp;
        return $availabilitySearchRsp;
    }


    public function AirFareDisplayReq(AirFareDisplayReq $airFareDisplayReq)
    {


        $airFareDisplayRsp = new AirFareDisplayRsp;
        return $airFareDisplayRsp;
    }


    public function SeatMapReq(SeatMapReq $seatMapReq)
    {


        $seatMapRsp = new SeatMapRsp;
        return $seatMapRsp;
    }


    public function AirRefundQuoteReq(AirRefundQuoteReq $airRefundQuoteReq)
    {


        $airRefundQuoteRsp = new AirRefundQuoteRsp;
        return $airRefundQuoteRsp;
    }


    public function AirRefundReq(AirRefundReq $airRefundReq)
    {


        $airRefundRsp = new AirRefundRsp;
        return $airRefundRsp;
    }


    public function AirTicketingReq(AirTicketingReq $airTicketingReq)
    {


        $airTicketingRsp = new AirTicketingRsp;
        return $airTicketingRsp;
    }


    public function AirVoidDocumentReq(AirVoidDocumentReq $airVoidDocumentReq)
    {


        $airVoidDocumentReq = new AirVoidDocumentReq;
        return $airVoidDocumentReq;
    }


    public function AirRetrieveDocumentReq(AirRetrieveDocumentReq $airRetrieveDocumentReq)
    {


        $airRetrieveDocumentRsp = new AirRetrieveDocumentRsp;
        return $airRetrieveDocumentRsp;
    }


    public function AirExchangeReq(AirExchangeReq $airExchangeReq)
    {


        $airExchangeRsp = new AirExchangeRsp;
        return $airExchangeRsp;
    }


    public function AirExchangeQuoteReq(AirExchangeQuoteReq $airExchangeQuoteReq)
    {


        $airExchangeQuoteRsp = new AirExchangeQuoteRsp;
        return $airExchangeQuoteRsp;
    }


    public function AirExchangeTicketingReq(AirExchangeTicketingReq $airExchangeTicketingReq)
    {


        $airExchangeTicketingRsp = new AirExchangeTicketingRsp;
        return $airExchangeTicketingRsp;
    }


    public function AirMerchandisingOfferAvailabilityReq(AirMerchandisingOfferAvailabilityReq $airMerchandisingOfferAvailabilityReq)
    {

        $airMerchandisingOfferAvailabilityRsp = new AirMerchandisingOfferAvailabilityRsp;
        return $airMerchandisingOfferAvailabilityRsp;
    }


    public function AirUpsellSearchReq(AirUpsellSearchReq $airUpsellSearchReq)
    {

        $airUpsellSearchRsp = new AirUpsellSearchRsp;
        return $airUpsellSearchRsp;
    }


    public function flightTimeTableReq(FlightTimeTableReq $flightTimeTableReq)
    {

        $flightTimeTableRsp = new FlightTimeTableRsp;
        return $flightTimeTableRsp;
    }


    public function AirPrePayReq(AirPrePayReq $airPrePayReq)
    {

        $airPrePayRsp = new AirPrePayRsp;
        return $airPrePayRsp;
    }


    public function EMDRetrieveReq(EMDRetrieveReq $eMDRetrieveReq)
    {

        $eMDRetrieveRsp = new EMDRetrieveRsp;
        return $eMDRetrieveRsp;
    }


    public function EMDIssuanceReq(EMDIssuanceReq $eMDIssuanceReq)
    {

        $eMDIssuanceRsp = new EMDIssuanceRsp;
        return $eMDIssuanceRsp;
    }


    public function AirMerchandisingDetailsReq(AirMerchandisingDetailsReq $airMerchandisingDetailsReq)
    {

        $airMerchandisingDetailsRsp = new AirMerchandisingDetailsRsp;
        return $airMerchandisingDetailsRsp;
    }


    public function FlightInformationReq(FlightInformationReq $flightInformationReq)
    {

        $flightInformationRsp = new FlightInformationRsp;
        return $flightInformationRsp;
    }


    public function FlightDetailsReq(FlightDetailsReq $flightDetailsReq)
    {

        $flightDetailsRsp = new FlightDetailsRsp;
        return $flightDetailsRsp;
    }


}