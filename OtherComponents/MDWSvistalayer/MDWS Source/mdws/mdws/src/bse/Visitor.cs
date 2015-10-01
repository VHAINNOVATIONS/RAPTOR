using System;

namespace gov.va.medora.mdws.bse
{
    public class Visitor
    {
        string securityToken;
        string name;
        string ssn;
        string uid;
        string siteId;
        string siteName;
        string phone;

        public Visitor() { }

        public string SecurityToken
        {
            get { return securityToken; }
            set { securityToken = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string SSN
        {
            get { return ssn; }
            set { ssn = value; }
        }

        public string UID
        {
            get { return uid; }
            set { uid = value; }
        }

        public string SiteID
        {
            get { return siteId; }
            set { siteId = value; }
        }

        public string SiteName
        {
            get { return siteName; }
            set { siteName = value; }
        }

        public string Phone
        {
            get { return phone; }
            set { phone = value; }
        }

        public IPrincipal Principal
        {
            get
            {
                string s = ssn + '^' + name + '^' + siteName + '^' + siteId + '^' + uid + '^' + phone + '^';
                return new VistaPrincipal(siteId, s);
            }
        }
    }
}
