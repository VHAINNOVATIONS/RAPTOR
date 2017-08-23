using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ProblemTO : AbstractTO
    {
        public bool removed;
        public bool verified;
        public TaggedNoteArray comments;
        public TaggedText acuity;
        public string id;
        public string status;
        public string providerNarrative;
        public string onsetDate;
        public string modifiedDate;
        public string resolvedDate;
        public string exposures;
        public string noteNarrative;
        public string priority;
        public AuthorTO observer;
        public TaggedText facility;
        public ObservationTypeTO type;
        public string comment;
        public TaggedTextArray organizationalProperties;
        public string timestamp;

        public ProblemTO() { }

        public ProblemTO(Problem mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.resolvedDate = mdo.ResolvedDate;
            this.removed = mdo.Removed;
            this.verified = mdo.Verified;
            if (mdo.Comments != null && mdo.Comments.Count > 0)
            {
                Note[] noteComments = new Note[mdo.Comments.Count];
                mdo.Comments.CopyTo(noteComments, 0);
                this.comments = new TaggedNoteArray("comments", noteComments);
            }
            this.acuity = new TaggedText(mdo.Acuity);
            this.id = mdo.Id;
            this.status = mdo.Status;
            this.providerNarrative = mdo.ProviderNarrative;
            this.onsetDate = mdo.OnsetDate;
            this.modifiedDate = mdo.ModifiedDate;
            this.exposures = mdo.Exposures;
            this.noteNarrative = mdo.NoteNarrative;
            this.priority = mdo.Priority;
            this.resolvedDate = mdo.ResolvedDate;
            if (mdo.Observer != null)
            {
                this.observer = new AuthorTO(mdo.Observer);
            }
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            if (mdo.Type != null)
            {
                this.type = new ObservationTypeTO(mdo.Type);
            }
            this.comment = mdo.Comment;
            if (mdo.OrganizationProperties != null && mdo.OrganizationProperties.Count > 0)
            {
                this.organizationalProperties = new TaggedTextArray(mdo.OrganizationProperties);
            }
            this.timestamp = mdo.Timestamp;
        }
    }
}
