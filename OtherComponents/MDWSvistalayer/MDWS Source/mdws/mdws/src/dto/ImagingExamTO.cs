using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class ImagingExamTO : AbstractTO
    {
        public string accessionNum;
        public string casenum;
        public string encounterId;
        public SiteTO facility;
        public bool hasImages;
        public string id;
        public string imagingType;
        public string interpretation;
        public HospitalLocationTO imagingLocation;
        public CptCodeTO[] modifiers;
        public string name;
        public OrderTO order;
        public UserTO provider;
        public RadiologyReportTO[] reports;
        public string reportId;
        public string status;
        public string timestamp;
        public CptCodeTO type;

        public ImagingExamTO() { /* parameterless constructor */ } 

        public ImagingExamTO(ImagingExam mdo)
        {
            this.accessionNum = mdo.AccessionNumber;
            this.casenum = mdo.CaseNumber;

            if (mdo.Encounter != null)
            {
                this.encounterId = mdo.Encounter.Id;
            }

            this.facility = new SiteTO(mdo.Facility);
            this.hasImages = mdo.HasImages;
            this.id = mdo.Id;
            this.imagingLocation = new HospitalLocationTO(mdo.ImagingLocation);
            this.imagingType = mdo.ImagingType;
            this.interpretation = mdo.Interpretation;

            if (mdo.Modifiers != null && mdo.Modifiers.Count > 0)
            {
                this.modifiers = new CptCodeTO[mdo.Modifiers.Count];
                for (int i = 0; i < mdo.Modifiers.Count; i++)
                {
                    this.modifiers[i] = new CptCodeTO(mdo.Modifiers[i]);
                }
            }

            this.name = mdo.Name;
            this.order = new OrderTO(mdo.Order);
            this.provider = new UserTO(mdo.Provider);
            this.reportId = mdo.ReportId;

            if (mdo.Reports != null && mdo.Reports.Count > 0)
            {
                this.reports = new RadiologyReportTO[mdo.Reports.Count];
                for (int i = 0; i < mdo.Reports.Count; i++)
                {
                    this.reports[i] = new RadiologyReportTO(mdo.Reports[i]);
                }
            }

            this.status = mdo.Status;
            this.timestamp = mdo.Timestamp;
            this.type = new CptCodeTO(mdo.Type);
        }
    }
}