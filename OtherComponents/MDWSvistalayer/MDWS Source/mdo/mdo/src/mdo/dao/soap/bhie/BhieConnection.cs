using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using Microsoft.Web.Services3;
using Microsoft.Web.Services3.Addressing;
using System.Xml;

namespace gov.va.medora.mdo.dao.soap.bhie
{
    public class BhieConnection : IConnection
    {
        private string url = "http://10.2.28.197:7010/axis/services/FrameworkWebAccessCN";
        private string bhieNamespace = "http://vistaweb.webservices.fhie.gov/";
        private string action;

        private BhieSoapClient soapClient;

        public BhieConnection(string action) 
        {
            this.soapClient = new BhieSoapClient(url);
            this.action = this.bhieNamespace + action;
        }

        public override object query(string soapString)
        {
            SoapEnvelope requestEnvelope = new SoapEnvelope();
            requestEnvelope.LoadXml(soapString);

            SoapEnvelope returnEnvelope = soapClient.RequestResponseMethod(action,requestEnvelope);
            return returnEnvelope;
        }

        #region Non-implemented methods
        public override void connect() { }
        public override void disconnect() { }
        public override string getWelcomeMessage()
        {
            return null;
        }
        public override DateTime getTimestamp()
        {
            return new DateTime();
        }
        public override StringDictionary getPatientTypes()
        {
            return null;
        }
        #endregion
    }
}
