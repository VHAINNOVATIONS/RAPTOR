using System;
using System.Collections.Generic;
using System.Text;
using Microsoft.Web.Services3;
using Microsoft.Web.Services3.Addressing;
using System.Xml;

namespace gov.va.medora.mdo.dao.bhie
{
    public class BhieNoteDao
    {
        BhieConnection cxn = null;

        public BhieNoteDao(Connection cxn)
        {
            this.cxn = (BhieConnection)cxn;
        }

        public BhieNoteDao() {}

        public Note[] getNotes(String fromDate, String toDate, int nNotes)
        {
            string soapString = "<soapenv:Envelope " +
                "xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" " +
                "xmlns:vis=\"http://vistaweb.webservices.fhie.gov\">" +
                "<soapenv:Header/>" +
                    "<soapenv:Body>" +
                        "<vis:getNotes>" +
                            "<arg_0_0>" +
                                "<active>true</active>" +
                                "<endDate>2010-06-30T04:00:00.000Z</endDate>" +
                                "<externalUserId>9876</externalUserId>" +
                                "<externalUserName>testuser</externalUserName>" +
                                "<itemId>ITEM1</itemId>" +
                                "<patientId>DNS:FHIE.GOV/VA0/1006184063V088473</patientId>" +
                                "<startDate>2000-06-30T04:00:00.000Z</startDate>" +
                                "<status>STATUS1</status>" +
                                "<userFacilityCode>506</userFacilityCode>" +
                                "<userFacilityName>Ann Arbor, MI</userFacilityName>" +
                                "<itemIds></itemIds>" +
                            "</arg_0_0>" +
                        "</vis:getNotes>" +
                    "</soapenv:Body>" +
                "</soapenv:Envelope>";

            SoapEnvelope response = (SoapEnvelope)cxn.query(soapString);
            return null;
        }
    }
}
