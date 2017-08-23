using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;

namespace gov.va.medora.mdws.dto
{
    public class NoteTO : AbstractTO
    {
        public string id = "";
        public string timestamp = "";
        public string admitTimestamp = "";
        public string dischargeTimestamp = "";
        public string serviceCategory = "";
        public string localTitle = "";
        public string standardTitle = "";
        public AuthorTO author;
        public HospitalLocationTO location;
        public string text = "";
        public bool hasAddendum = false;
        public bool isAddendum = false;
        public string originalNoteID = "";
        public bool hasImages = false;
        public string itemId = "";
        public AuthorTO approvedBy;
        public string status = "";
        public string type = "";
        public string signatureTimestamp;

        public NoteTO() { }

        public NoteTO(Note mdoNote)
        {
            if (mdoNote == null) // || ((mdoNote.Id == null || mdoNote.Id == "") && mdoNote.ApprovedBy == null))
            {
                return;
            }
            this.id = mdoNote.Id;
            this.timestamp = mdoNote.Timestamp;
            this.admitTimestamp = mdoNote.AdmitTimestamp;
            this.dischargeTimestamp = mdoNote.DischargeTimestamp;
            this.localTitle = mdoNote.LocalTitle;
            this.standardTitle = mdoNote.StandardTitle;
            this.serviceCategory = mdoNote.ServiceCategory;
            if (mdoNote.Author != null)
            {
                this.author = new AuthorTO(mdoNote.Author);
            }
            if (mdoNote.Location != null)
            {
                this.location = new HospitalLocationTO(mdoNote.Location);
            }
            // Why is this here? Site is not the same as location... 
            //else if (!String.IsNullOrEmpty(mdoNote.SiteId.Id) || !String.IsNullOrEmpty(mdoNote.SiteId.Name))
            //{
            //    HospitalLocation hl = new HospitalLocation(mdoNote.SiteId.Id, mdoNote.SiteId.Name);
            //    this.location = new HospitalLocationTO(hl);
            //}
            this.text = mdoNote.Text;
            this.hasAddendum = mdoNote.HasAddendum;
            this.isAddendum = mdoNote.IsAddendum;
            this.originalNoteID = mdoNote.OriginalNoteId;
            this.hasImages = mdoNote.HasImages;
            if (mdoNote.ApprovedBy != null)
            {
                this.approvedBy = new AuthorTO(mdoNote.ApprovedBy);
            }
            this.status = mdoNote.Status;
            this.type = mdoNote.Type;
            this.signatureTimestamp = mdoNote.ProcTimestamp;
        }
    }
}
