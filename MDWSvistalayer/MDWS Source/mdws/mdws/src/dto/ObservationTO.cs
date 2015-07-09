using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

/// <summary>
/// Summary description for ObservationTO
/// </summary>

namespace gov.va.medora.mdws.dto
{
    public class ObservationTO : AbstractTO
    {
        public AuthorTO observer;
        public AuthorTO recorder;
        public string timestamp;
        public TaggedText facility;
        public HospitalLocationTO location;
        public ObservationTypeTO type;
        public string comment;

        public ObservationTO() { }

        public ObservationTO(Observation mdo)
        {
            if (mdo == null)
            {
                return;
            }
            if (mdo.Observer != null)
            {
                this.observer = new AuthorTO(mdo.Observer);
            }
            if (mdo.Recorder != null)
            {
                this.recorder = new AuthorTO(mdo.Recorder);
            }
            this.timestamp = mdo.Timestamp;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            if (mdo.Location != null)
            {
                this.location = new HospitalLocationTO(mdo.Location);
            }
            if (mdo.Type != null)
            {
                this.type = new ObservationTypeTO(mdo.Type);
            }
            this.comment = mdo.Comment;
        }
    }
}
