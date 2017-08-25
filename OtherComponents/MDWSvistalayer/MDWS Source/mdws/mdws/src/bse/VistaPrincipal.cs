using System;

namespace gov.va.medora.mdws.bse
{
    public interface IPrincipal
    {
        string Name { get; }
        string Value { get; }
        string UserName { get; }
        string UserUid { get; }
        string MuiId { get; }
        string SiteName { get; }
        string SiteId { get; }
    }

    public class VistaPrincipal : IPrincipal
    {
        protected string myName;
        protected string mySiteId;
        protected string myValue;

        const int USERNAME_FLD = 2;
        const int USERDUZ_FLD = 5;
        const int MUIID_FLD = 1;
        const int SITENAME_FLD = 3;

        public VistaPrincipal(string siteId, string value)
        {
            mySiteId = siteId;
            myValue = value;
        }

        public string Name
        {
            get { return "vista:" + mySiteId; }
        }

        public string SiteId
        {
            get { return mySiteId; }
        }

        public string Value
        {
            get { return myValue; }
        }

        public static bool IsVistaPrincipal(IPrincipal principal)
        {
            return principal.GetType().IsInstanceOfType(typeof(VistaPrincipal));
        }

        public string UserName
        {
            get { return StringUtils.piece(myValue, StringUtils.CARET, USERNAME_FLD); }
        }

        public string UserUid
        {
            get { return StringUtils.piece(myValue, StringUtils.CARET, USERDUZ_FLD); }
        }

        public string MuiId
        {
            get { return StringUtils.piece(myValue, StringUtils.CARET, MUIID_FLD); }
        }

        public string SiteName
        {
            get { return StringUtils.piece(myValue, StringUtils.CARET, SITENAME_FLD); }
        }

    }
}
