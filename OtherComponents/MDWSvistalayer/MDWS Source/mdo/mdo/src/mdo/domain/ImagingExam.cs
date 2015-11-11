using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo
{
    public class ImagingExam
    {
        public string ClinicalHistory { get; set; }

        string accessionNum;
        string casenum;
        string cpt;
        Encounter _encounter;
        Site _facility;
        bool _hasImages;
        string _id;
        string _imagingType;
        string _interpretation;
        HospitalLocation _imagingLocation;
        string modality;
        IList<CptCode> _modifiers;
        string name;
        Order _order;
        User _provider;
        IList<RadiologyReport> _reports;
        string reportId;
        string status;
        string timestamp;
        CptCode _type;


        public ImagingExam() { }


        public IList<RadiologyReport> Reports
        {
            get { return _reports; }
            set { _reports = value; }
        }

        public HospitalLocation ImagingLocation
        {
            get { return _imagingLocation; }
            set { _imagingLocation = value; }
        }


        public string Interpretation
        {
            get { return _interpretation; }
            set { _interpretation = value; }
        }
        public User Provider
        {
            get { return _provider; }
            set { _provider = value; }
        }


        public string ImagingType
        {
            get { return _imagingType; }
            set { _imagingType = value; }
        }
        public string Id
        {
            get { return _id; }
            set { _id = value; }
        }

        public Site Facility
        {
            get { return _facility; }
            set { _facility = value; }
        }

        public IList<CptCode> Modifiers
        {
            get { return _modifiers; }
            set { _modifiers = value; }
        }


        public bool HasImages
        {
            get { return _hasImages; }
            set { _hasImages = value; }
        }

        public Encounter Encounter
        {
            get { return _encounter; }
            set { _encounter = value; }
        }

        public Order Order
        {
            get { return _order; }
            set { _order = value; }
        }

        public CptCode Type
        {
            get { return _type; }
            set { _type = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public string Modality
        {
            get { return modality; }
            set { modality = value; }
        }

        public string CaseNumber
        {
            get { return casenum; }
            set { casenum = value; }
        }

        public string AccessionNumber
        {
            get { return accessionNum; }
            set { accessionNum = value; }
        }

        public string CptCode
        {
            get { return cpt; }
            set { cpt = value; }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }

        public string ReportId
        {
            get { return reportId; }
            set { reportId = value; }
        }

        const string DAO_NAME = "IRadiologyDao";

        internal static IRadiologyDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getRadiologyDao(cxn);
        }

        public static RadiologyReport getReportText(AbstractConnection cxn, string dfn, string accessionNumber)
        {
            return getDao(cxn).getImagingReport(dfn,accessionNumber);
        }

    }
}
