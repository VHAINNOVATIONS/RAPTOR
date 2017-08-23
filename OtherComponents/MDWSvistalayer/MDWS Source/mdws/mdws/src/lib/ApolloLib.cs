using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using gov.va.medora.mdws.dto;
using System.Reflection;

namespace gov.va.medora.mdws.apollo
{
    public class ApolloLib
    {
        MySession mySession;

        public ApolloLib(MySession theSession)
        {
            mySession = theSession;
        }

        public TaggedTextArray disconnectRemoteSites()
        {
            //string logMsg = "disconnectRemoteSites()";
            //mySession.log.Info(logMsg);
            ConnectionLib lib = new ConnectionLib(mySession);
            TaggedTextArray result = lib.disconnectRemoteSites();
            //if (result.fault != null)
            //{
            //    mySession.log.Error(logMsg + ": " + result.fault.message);
            //}
            //else
            //{
            //    mySession.log.Info(logMsg + ": " + result.count + " disconnected");
            //}

            return result;
        }

    }
}
