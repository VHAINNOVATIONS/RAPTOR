using System;
using System.Collections.Generic;

namespace gov.va.medora.mdo.dao
{
    public abstract class AbstractCredentials
    {
        string acctName;
        string acctPwd;
        string authToken;
        DataSource authSrc;
        string fedId;
        string locId;
        string subjName;
        string subjPhone;

        string securityPhrase;

        public AbstractCredentials() { }

        public string AccountName
        {
            get { return acctName; }
            set { acctName = value; }
        }

        public string AccountPassword
        {
            get { return acctPwd; }
            set { acctPwd = value; }
        }

        public DataSource AuthenticationSource
        {
            get { return authSrc; }
            set { authSrc = value; }
        }

        public string FederatedUid
        {
            get { return fedId; }
            set { fedId = value; }
        }

        public string LocalUid
        {
            get { return locId; }
            set { locId = value; }
        }

        public string SubjectName
        {
            get { return subjName; }
            set { subjName = value; }
        }

        public string SubjectPhone
        {
            get { return subjPhone; }
            set { subjPhone = value; }
        }

        public string AuthenticationToken
        {
            get { return authToken; }
            set { authToken = value; }
        }

        public string SecurityPhrase
        {
            get { return securityPhrase; }
            set { securityPhrase = value; }
        }

        public abstract bool AreTest
        {
            get;
        }

        public abstract bool Complete
        {
            get;
        }

        public static AbstractCredentials getCredentialsForCxn(AbstractConnection cxn)
        {
            string protocol = cxn.DataSource.Protocol;
            if (protocol == "VISTA" || protocol == "FHIE" || protocol == "RPMS" || protocol == "MOCK" || protocol == "XVISTA" || protocol == "PVISTA")
            {
                return new gov.va.medora.mdo.dao.vista.VistaCredentials();
            } 
            else if (protocol == "CDW") 
            {
                return new gov.va.medora.mdo.dao.sql.cdw.CdwCredentials();
            }
            //if (String.Equals("RDW", protocol, StringComparison.CurrentCultureIgnoreCase))
            //{
            //    return new gov.va.medora.mdo.dao.soap.rdw.RdwCredentials();
            //}
            return null;
        }
    }
}
