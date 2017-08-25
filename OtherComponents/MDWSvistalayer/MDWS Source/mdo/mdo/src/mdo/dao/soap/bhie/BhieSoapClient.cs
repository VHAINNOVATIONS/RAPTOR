using System;
using System.Collections.Generic;
using System.Text;
using Microsoft.Web.Services3;
using Microsoft.Web.Services3.Messaging;
using Microsoft.Web.Services3.Addressing;

namespace gov.va.medora.mdo.dao.bhie
{
    public class BhieSoapClient : SoapClient
    {
        public BhieSoapClient(string url)
        {
            Uri destinationUri = new Uri(url);
            this.Destination = new EndpointReference(destinationUri);
        }

        [SoapMethod("RequestResponseMethod")]
        public SoapEnvelope RequestResponseMethod(string action, SoapEnvelope envelope)
        {
            SoapEnvelope result = base.SendRequestResponse(action, envelope);
            return result;
        }
    }
}
