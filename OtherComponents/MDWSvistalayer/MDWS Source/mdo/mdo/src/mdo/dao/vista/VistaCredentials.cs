using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaCredentials : AbstractCredentials
    {
        List<string> productionSitecodes = new List<string>(new string[]
            { 
                "402", "405", "518", "523", "608", "631", "650", "689",                 //VISN 1
                "528",                                                                  //VISN 2
                "526", "561", "620", "630", "632",                                      //VISN 3
                "460", "503", "529", "540", "542", "562", "595", "642", "646", "693",   //VISN 4
                "512", "613", "688",                                                    //VISN 5
                "517", "558", "565", "590", "637", "652", "658", "659",                 //VISN 6
                "508", "509", "521", "534", "544", "557", "619", "679",                 //VISN 7
                "516", "546", "548", "573", "672", "673", "675",                        //VISN 8
                "581", "596", "603", "621", "626", "614",                               //VISN 9
                "538", "539", "552", "541", "757",                                      //VISN 10
                "655", "515", "553", "583", "506", "550", "610",                        //VISN 11
                "695", "578", "585", "556", "676", "537", "607",                        //VISN 12
                "589", "657",                                                           //VISN 15
                "502", "598", "564", "586", "580", "623", "635", "667", "520", "629",   //VISN 16
                "674", "671", "549",                                                    //VISN 17
                "504", "756", "501", "649", "678", "519", "644",                        //VISN 18
                "741", "554", "436", "442", "575", "666", "660",                        //VISN 19
                "463", "531", "687", "648", "668", "663", "653", "692",                 //VISN 20
                "612", "662", "570", "459", "640", "654", "358",                        //VISN 21
                "691", "605", "600", "664", "593",                                      //VISN 22
                "437", "618", "636", "438", "656", "568",                               //VISN 23
                "200",                                                                  //BHIE
                "100"                                                                   //CLAIMS
            });

        public VistaCredentials() : base() {}

        public override bool AreTest
        {
            get 
            {
                // Is the user's sitecode a test system?
                if (null != AuthenticationSource && !productionSitecodes.Contains(AuthenticationSource.SiteId.Id))
                {
                    return true;
                }

                // Is the SSN valid?
                if (!VistaSocSecNum.isValid(FederatedUid))
                {
                    return true;
                }
                return false;
            }
        }

        public override bool Complete
        {
            get 
            {
                if (String.IsNullOrEmpty(FederatedUid) || String.IsNullOrEmpty(SubjectName))
                {
                    return false;
                }
                return true;
            }
        }

        public bool AreLoginAt(string sitecode)
        {
            if (String.IsNullOrEmpty(AuthenticationSource.SiteId.Id))
            {
                throw new UnauthorizedAccessException("Null or empty authenticator ID");
            }
            return (AuthenticationSource.SiteId.Id == sitecode && AreLogin);
        }

        string getCredentialTypeAt(string cxnSitecode)
        {
            if (AreLoginAt(cxnSitecode))
            {
                return VistaConstants.LOGIN_CREDENTIALS;
            }
            if (AreBseVisit)
            {
                return VistaConstants.BSE_CREDENTIALS_V2WEB;
            }
            if (AreNonBseVisit)
            {
                return VistaConstants.NON_BSE_CREDENTIALS;
            }
            throw new UnauthorizedAccessException("Invalid credentials");
        }

        public bool AreVisit
        {
            get
            {
                if (AreBseVisit || AreNonBseVisit)
                {
                    return true;
                }
                return false;
            }
        }

        public bool AreLogin
        {
            get 
            {
                if (!String.IsNullOrEmpty(AccountName) &&
                    !String.IsNullOrEmpty(AccountPassword))
                {
                    return true;
                }
                return false;
            }
        }

        internal string areValidBse
        {
            get
            {
                if (String.IsNullOrEmpty(AccountPassword))
                {
                    return "Missing security phrase";
                }
                if (AccountPassword == VistaConstants.NON_BSE_SECURITY_PHRASE)
                {
                    return "Non-BSE security phrase";
                }
                if (String.IsNullOrEmpty(AuthenticationToken))
                {
                    return "Missing authentication token";
                }
                return "OK";
            }
        }

        public bool AreBseVisit
        {
            get
            {
                if (!String.IsNullOrEmpty(AccountPassword) &&
                    AccountPassword != VistaConstants.NON_BSE_SECURITY_PHRASE &&
                    !String.IsNullOrEmpty(AuthenticationToken) &&
                    hasVisitData())
                {
                    return true;
                }
                return false;
            }
        }

        public bool AreNonBseVisit
        {
            get
            {
                if (!String.IsNullOrEmpty(AccountPassword) &&
                    AccountPassword == VistaConstants.NON_BSE_SECURITY_PHRASE &&
                    hasVisitData())
                {
                    return true;
                }
                return false;
            }
        }

        internal bool hasVisitData()
        {
            if (!String.IsNullOrEmpty(FederatedUid) &&
                !String.IsNullOrEmpty(SubjectName) &&
                !String.IsNullOrEmpty(LocalUid) &&
                !String.IsNullOrEmpty(AuthenticationSource.SiteId.Id) &&
                !String.IsNullOrEmpty(AuthenticationSource.SiteId.Name) &&
                !String.IsNullOrEmpty(SubjectPhone))
            {
                return true;
            }
            return false;
        }
    }
}
