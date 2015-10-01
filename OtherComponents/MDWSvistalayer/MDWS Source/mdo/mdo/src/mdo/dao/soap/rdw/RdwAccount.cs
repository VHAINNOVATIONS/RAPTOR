using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.rdw;
using System.Net;
using System.IO;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwAccount : AbstractAccount
    {
        RdwConnection _cxn;
        AbstractCredentials _creds;

        public RdwAccount(AbstractConnection cxn) : base(cxn)
        {
            if (!(cxn is RdwConnection) || cxn.DataSource == null || String.IsNullOrEmpty(cxn.DataSource.Provider))
            {
                throw new MdoException("Invalid connection type");
            }
            _cxn = (RdwConnection)cxn;
        }

        public override string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null)
        {
            if (credentials == null || String.IsNullOrEmpty(credentials.AccountName) || String.IsNullOrEmpty(credentials.AccountPassword))
            {
                throw new MdoException("Invalid RDW credenetials");
            }
            _creds = credentials;

            // see if provider was specified in datasource - use default from proxy code if not
            WebRequest cookieRequest = WebRequest.Create(String.IsNullOrEmpty(Cxn.DataSource.Provider) ? new Uri(new MDWSRPCs().Url) : new Uri(Cxn.DataSource.Provider));
            cookieRequest.Method = "POST";
            cookieRequest.ContentType = "application/x-www-form-urlencoded";
            string postBody = "CacheUserName=" + _creds.AccountName + "&CachePassword=" + _creds.AccountPassword + "&CacheLogin=Login";
            cookieRequest.ContentLength = postBody.Length;

            Stream requestStream = cookieRequest.GetRequestStream();
            requestStream.Write(System.Text.Encoding.ASCII.GetBytes(postBody), 0, postBody.Length);
            requestStream.Close();
            WebResponse cookieResponse = cookieRequest.GetResponse();

            // expect the response URI to contain the session token for subsequent requests - throw an error if the strings are the same
            if (String.Equals(cookieResponse.ResponseUri.ToString(), _cxn.DataSource.Provider))
            {
                throw new MdoException("Authentication failed. Response URI did not contain session token");
            }

            _cxn.DataSource.Provider = cookieResponse.ResponseUri.ToString(); // set the provider to the URL containing the cookie
            _creds.AuthenticationToken = cookieResponse.ResponseUri.ToString();
            this.isAuthorized = true;
            return "OK";
        }

        public override User authorize(AbstractCredentials credentials, AbstractPermission permission)
        {
            if (_creds == null)
            {
                throw new MdoException("Invalid RDW credenetials. Must authenticate first");
            }
            return new User() { UserName = _creds.AccountName, Pwd = _creds.AccountPassword };
        }

        public override User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            authenticate(credentials);
            return authorize(credentials, permission);
        }
    }
}
