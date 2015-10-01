using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaAuthenticationToken : AbstractAuthenticationToken
    {
        //string sitecode;
        //string sitename;
        //string duz;
        //SocSecNum ssn;
        //PersonName username;
        //string phone;
        //const char delimiter = '_';

        public static string NATIONAL_ID = "NationalId";
        public static string LOCAL_ID = "LocalId";
        public static string SITE_ID = "SiteId";
        public static string SITE_NAME = "SiteName";
        public static string USER_NAME = "UserName";
        public static string PHONE = "Phone";

        //public string Sitecode
        //{
        //    get { return Items["sitecode"]; }
        //    set { sitecode = value; }
        //}

        //public string Sitename
        //{
        //    get { return sitename; }
        //    set { sitename = value; }
        //}

        //public string LocalId
        //{
        //    get { return duz; }
        //    set { duz = value; }
        //}

        //public string NationalId
        //{
        //    get { return ssn.toString(); }
        //    set { ssn = new SocSecNum(value); }
        //}

        //public string Username
        //{
        //    get { return username.getLastNameFirst(); }
        //    set { username = new PersonName(value); }
        //}

        //public string Phone
        //{
        //    get { return phone; }
        //    set { phone = value; }
        //}

        //public string SecurityToken
        //{
        //    get{return sitecode + delimiter + duz;}
        //}

    }
}
