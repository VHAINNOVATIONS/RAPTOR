using System;
using System.Collections.Specialized;
using System.Collections;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class Patient : Person
    {
        const int INPATIENT = 1;
        const int OUTPATIENT = 2;

        public String EligibilityCode { get; set; }
        public string EDIPI { get; set; }
        string mpiPid;
        string localPid;
        StringDictionary sitePids;
        string vendorPid;
        string localSiteId;
        string mpiChecksum;
        bool locallyAssignedMpiPid;
        HospitalLocation location;
        string cwad;
        bool restricted;
        string admitTimestamp;
        bool serviceConnected;
        int scPercent;
        bool inpatient;
        string deceasedDate;
        KeyValuePair<int, string> confidentiality;
        bool needsMeansTest;
        string currentMeansStatus;
        StringDictionary patientFlags;
        string cmorSiteId;
        string activeInsurance;
        bool hasInsurance;
        bool testPatient;
        KeyValuePair<string, string> preferredFacility;
        string patientType;
        bool isVeteran;
        Team team;
        SiteId[] siteIDs;

        const string DAO_NAME = "IPatientDao";

        public Patient() { }

        public string MpiPid
        {
            get
            {
                return mpiPid;
            }
            set
            {
                mpiPid = value;
            }
        }

        public string LocalPid
        {
            get
            {
                return localPid;
            }
            set
            {
                localPid = value;
            }
        }

        public StringDictionary SitePids
        {
            get
            {
                return sitePids;
            }
            set
            {
                sitePids = value;
            }
        }

        public string VendorPid
        {
            get
            {
                return vendorPid;
            }
            set
            {
                vendorPid = value;
            }
        }

        public string LocalSiteId
        {
            get
            {
                return localSiteId;
            }
            set
            {
                localSiteId = value;
            }
        }

        public string MpiChecksum
        {
            get { return mpiChecksum; }
            set { mpiChecksum = value; }
        }

        public HospitalLocation Location
        {
            get
            {
                return location;
            }
            set
            {
                location = value;
            }
        }

        public string Cwad
        {
            get
            {
                return cwad;
            }
            set
            {
                cwad = value;
            }
        }

        public bool IsRestricted
        {
            get
            {
                return restricted;
            }
            set
            {
                restricted = value;
            }
        }

        public string AdmitTimestamp
        {
            get
            {
                return admitTimestamp;
            }
            set
            {
                admitTimestamp = value;
            }
        }

        public bool IsServiceConnected
        {
            get
            {
                return serviceConnected;
            }
            set
            {
                serviceConnected = value;
            }
        }

        public int ScPercent
        {
            get
            {
                return scPercent;
            }
            set
            {
                scPercent = value;
            }
        }

        public bool IsInpatient
        {
            get
            {
                return inpatient;
            }
            set
            {
                inpatient = value;
            }
        }

        public string DeceasedDate
        {
            get { return deceasedDate; }
            set { deceasedDate = value; }
        }

        public KeyValuePair<int, string> Confidentiality
        {
            get { return confidentiality; }
            set { confidentiality = value; }
        }

        public bool NeedsMeansTest
        {
            get { return needsMeansTest; }
            set { needsMeansTest = value; }
        }

        public StringDictionary PatientFlags
        {
            get { return patientFlags; }
            set { patientFlags = value; }
        }

        public string CmorSiteId
        {
            get { return cmorSiteId; }
            set { cmorSiteId = value; }
        }

        public string ActiveInsurance
        {
            get { return activeInsurance; }
            set { activeInsurance = value; }
        }

        public bool IsTestPatient
        {
            get { return testPatient; }
            set { testPatient = value; }
        }

        public string CurrentMeansStatus
        {
            get { return currentMeansStatus; }
            set { currentMeansStatus = value; }
        }

        public bool HasInsurance
        {
            get { return hasInsurance; }
            set { hasInsurance = value; }
        }

        public KeyValuePair<string, string> PreferredFacility
        {
            get { return preferredFacility; }
            set { preferredFacility = value; }
        }

        public string PatientType
        {
            get { return patientType; }
            set { patientType = value; }
        }

        public bool IsVeteran
        {
            get { return isVeteran; }
            set { isVeteran = value; }
        }

        public bool IsLocallyAssignedMpiPid
        {
            get { return locallyAssignedMpiPid; }
            set { locallyAssignedMpiPid = value; }
        }

        public SiteId[] SiteIDs
        {
            get { return siteIDs; }
            set { siteIDs = value; }
        }

        public Team Team
        {
            get { return team; }
            set { team = value; }
        }

        internal static IPatientDao getDao(AbstractConnection cxn)
        {
            if (!cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: unconnected");
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getPatientDao(cxn);
        }

        public static KeyValuePair<string, string> getPcpForPatient(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getPcpForPatient(pid);
        }
    }
}
