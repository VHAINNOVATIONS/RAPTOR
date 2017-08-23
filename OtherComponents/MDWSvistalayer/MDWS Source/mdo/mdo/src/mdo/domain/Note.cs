using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Note
    {
        public IList<Author> Signers { get; set; }
        string id;
        string timestamp;
        string serviceCategory;
        string documentDefinitionId;
        string localTitle;
        string standardTitle;
        Author author = null;
        Author cosigner;
        HospitalLocation location;
        string text;
        SiteId siteId;
        bool fHasAddendum = false;
        bool fIsAddendum = false;
        string originalNoteID;
        bool fHasImages = false;
        string admitTimestamp;
        string dischargeTimestamp;
        Author approvedBy;
        string status;
        string consultId;
        string surgicalProcId;
        string prfId;
        string parentId;
        string procId;
        string procTimestamp;
        string subject;
        string _type;

        public Note() { }

        public string Type
        {
            get { return _type; }
            set { _type = value; }
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public string ServiceCategory
        {
            get { return serviceCategory; }
            set { serviceCategory = value; }
        }

        public string DocumentDefinitionId
        {
            get { return documentDefinitionId; }
            set { documentDefinitionId = value; }
        }

        public string LocalTitle
        {
            get { return localTitle; }
            set { localTitle = value; }
        }

        public string StandardTitle
        {
            get { return standardTitle; }
            set { standardTitle = value; }
        }

        public Author Author
        {
            get { return author; }
            set { author = value; }
        }

        public HospitalLocation Location
        {
            get { return location; }
            set { location = value; }
        }

        public string Text
        {
            get { return text; }
            set { text = value; }
        }

        public SiteId SiteId
        {
            get { return siteId; }
            set { siteId = value; }
        }

        public bool HasAddendum
        {
            get { return fHasAddendum; }
            set { fHasAddendum = value; }
        }

        public bool IsAddendum
        {
            get { return fIsAddendum; }
            set { fIsAddendum = value; }
        }

        public string OriginalNoteId
        {
            get { return originalNoteID; }
            set { originalNoteID = value; }
        }

        public bool HasImages
        {
            get { return fHasImages; }
            set { fHasImages = value; }
        }

        public string AdmitTimestamp
        {
            get { return admitTimestamp; }
            set { admitTimestamp = value; }
        }

        public string DischargeTimestamp
        {
            get { return dischargeTimestamp; }
            set { dischargeTimestamp = value; }
        }

        public Author ApprovedBy
        {
            get { return approvedBy; }
            set { approvedBy = value; }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }

        public string ConsultId
        {
            get { return consultId; }
            set { consultId = value; }
        }

        public string SurgicalProcId
        {
            get { return surgicalProcId; }
            set { surgicalProcId = value; }
        }

        public string PrfId
        {
            get { return prfId; }
            set { prfId = value; }
        }

        public Author Cosigner
        {
            get { return cosigner; }
            set { cosigner = value; }
        }

        public string ParentId
        {
            get { return parentId; }
            set { parentId = value; }
        }

        public string Subject
        {
            get { return subject; }
            set { subject = value; }
        }

        public string ProcId
        {
            get { return procId; }
            set { procId = value; }
        }

        public string ProcTimestamp
        {
            get { return procTimestamp; }
            set { procTimestamp = value; }
        }
    }
}
