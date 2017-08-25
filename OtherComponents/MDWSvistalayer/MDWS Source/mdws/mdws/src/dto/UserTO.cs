using System;
using gov.va.medora.mdo;

/// <summary>
/// Summary description for UserTO
/// </summary>

namespace gov.va.medora.mdws.dto
{
    public class UserTO : AbstractTO
    {
        public string domain;
        public string name;
        public string SSN;
        public string DUZ;
        public string siteId;
        public string office;
        public string phone;
        public string pager;
        public string digitalPager;
        public string service;
        public string title;
        public string orderRole;
        public string userClass;
        public string greeting;
        public string siteMessage;
        public TaggedTextArray ids;
        public string emailAddress;
        public string username;
        public DemographicSetTO demographics;
        //public UserSecurityKeyArray securityKeys;
        //public UserOptionArray menuOptions;
        //public UserOptionArray delegatedOptions;
        //public TaggedTextArray divisions;

        public UserTO() { }

        public UserTO(User mdoUser)
        {
            if (mdoUser == null)
            {
                return;
            }
            this.name = mdoUser.Name == null ? "" : mdoUser.Name.getLastNameFirst();
            this.SSN = mdoUser.SSN == null ? "" : mdoUser.SSN.toString();
            this.DUZ = mdoUser.Uid;
            this.siteId = mdoUser.LogonSiteId == null ? "" : mdoUser.LogonSiteId.Id;
            this.office = mdoUser.Office;
            this.phone = mdoUser.Phone;
            this.pager = mdoUser.VoicePager;
            this.digitalPager = mdoUser.DigitalPager;
            if (mdoUser.Service != null)
            {
                this.service = mdoUser.Service.Name;
            }
            this.title = mdoUser.Title;
            this.orderRole = mdoUser.OrderRole;
            this.userClass = mdoUser.UserClass;
            this.greeting = mdoUser.Greeting;
            this.emailAddress = mdoUser.EmailAddress;
            this.username = mdoUser.UserName;
            this.demographics = new DemographicSetTO(mdoUser.Demographics);
            this.domain = mdoUser.Domain;
            //if (mdoUser.SecurityKeys != null)
            //{
            //    this.securityKeys = new UserSecurityKeyArray(mdoUser.SecurityKeys);
            //}
            //if (mdoUser.MenuOptions != null)
            //{
            //    this.menuOptions = new UserOptionArray(mdoUser.MenuOptions);
            //}
            //if (mdoUser.DelegatedOptions != null)
            //{
            //    this.delegatedOptions = new UserOptionArray(mdoUser.DelegatedOptions);
            //}
            //if (mdoUser.Divisions != null)
            //{
            //    this.divisions = new TaggedTextArray(mdoUser.Divisions);
            //}
        }
    }
}
