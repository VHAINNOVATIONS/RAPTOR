using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientTO : PersonTO
    {
        // Delphi has issues with using just Name as an accessor - patientName is a workaround
        public string eligibilityCode;
        public string patientName = "";
        public string mpiPid = "";
        public string mpiChecksum = "";
        public string localPid = "";
        public TaggedTextArray sitePids;
        public string vendorPid = "";
        public HospitalLocationTO location;
        public string cwad = "";
        public bool restricted;
        public string admitTimestamp = "";
        public bool serviceConnected;
        public int scPercent = 0;
        public bool inpatient;
        public string deceasedDate = "";
        public TaggedText confidentiality;
        public bool needsMeansTest;
        public TaggedTextArray patientFlags;
        public string cmorSiteId = "";
        public string activeInsurance = "";
        public bool isTestPatient;
        public string currentMeansStatus;
        public bool hasInsurance;
        public TaggedText preferredFacility;
        public string patientType;
        public bool isVeteran;
        public bool isLocallyAssignedMpiPid;
        public SiteArray sites;
        public TeamTO team;

        public PatientTO() { }

        public PatientTO(Patient mdo)
        {
            if (mdo == null)
            {
                return;
            }
            if (mdo.Relationships != null && mdo.Relationships.Count > 0)
            {
                this.relationships = new PersonArray(mdo.Relationships);
            }
            this.religion = mdo.Religion;
            this.employmentStatus = mdo.EmploymentStatus;
            this.occupation = mdo.Occupation;
            this.eligibilityCode = mdo.EligibilityCode;
            this.name = this.patientName = mdo.Name == null ? "" : mdo.Name.getLastNameFirst();
            this.ssn = mdo.SSN == null ? "" : mdo.SSN.toString();
            this.dob = mdo.DOB;
            this.gender = mdo.Gender;
            this.mpiPid = mdo.MpiPid;
            this.mpiChecksum = mdo.MpiChecksum;
            this.localPid = mdo.LocalPid;
            this.sitePids = mdo.SitePids == null || mdo.SitePids.Count == 0 ? null : new TaggedTextArray(mdo.SitePids);
            this.vendorPid = mdo.VendorPid;
            this.location = new HospitalLocationTO(mdo.Location);
            this.age = mdo.Age;
            this.cwad = mdo.Cwad;
            this.restricted = mdo.IsRestricted;
            //this.admitTimestamp = mdo.AdmitTimestamp.Year == 1 ? "" : mdo.AdmitTimestamp.ToString("yyyyMMdd.HHmmss");
            this.admitTimestamp = mdo.AdmitTimestamp;
            this.serviceConnected = mdo.IsServiceConnected;
            this.scPercent = mdo.ScPercent;
            this.inpatient = mdo.IsInpatient;
            //this.deceasedDate = mdo.DeceasedDate.Year == 1 ? "" : mdo.DeceasedDate.ToString("yyyyMMdd.HHmmss");
            this.deceasedDate = mdo.DeceasedDate;
            this.confidentiality = new TaggedText(mdo.Confidentiality);
            this.needsMeansTest = mdo.NeedsMeansTest;
            this.cmorSiteId = mdo.CmorSiteId;
            this.activeInsurance = mdo.ActiveInsurance;
            this.isTestPatient = mdo.IsTestPatient;
            this.maritalStatus = mdo.MaritalStatus;
            this.ethnicity = mdo.Ethnicity;
            this.currentMeansStatus = mdo.CurrentMeansStatus;
            this.hasInsurance = mdo.HasInsurance;
            this.preferredFacility = new TaggedText(mdo.PreferredFacility);
            this.patientType = mdo.PatientType;
            this.isVeteran = mdo.IsVeteran;
            this.patientFlags = new TaggedTextArray(mdo.PatientFlags);
            this.isLocallyAssignedMpiPid = mdo.IsLocallyAssignedMpiPid;
            if (mdo.HomeAddress != null)
            {
                this.homeAddress = new AddressTO(mdo.HomeAddress);
            }
            if (mdo.HomePhone != null)
            {
                this.homePhone = new PhoneNumTO(mdo.HomePhone);
            }
            if (mdo.CellPhone != null)
            {
                this.cellPhone = new PhoneNumTO(mdo.CellPhone);
            }
            if (mdo.SiteIDs != null)
            {
                Site[] a = new Site[mdo.SiteIDs.Length];
                for (int i = 0; i < mdo.SiteIDs.Length; i++)
                {
                    a[i] = new Site(mdo.SiteIDs[i].Id, mdo.SiteIDs[i].Name);
                    a[i].LastEventTimestamp = mdo.SiteIDs[i].LastSeenDate;
                    a[i].LastEventReason = mdo.SiteIDs[i].LastEvent;
                }
                this.sites = new SiteArray(a);
            }
            if (mdo.Team != null)
            {
                this.team = new TeamTO(mdo.Team);
            }

            if (mdo.Demographics != null && mdo.Demographics.Count > 0)
            {
                this.demographics = new DemographicSetTO[mdo.Demographics.Count];
                string[] keys = new string[mdo.Demographics.Count];
                mdo.Demographics.Keys.CopyTo(keys, 0);
                for (int i = 0; i < mdo.Demographics.Count; i++)
                {
                    this.demographics[i] = new DemographicSetTO(keys[i], mdo.Demographics[keys[i]]);
                }
            }
        }
    }
}
