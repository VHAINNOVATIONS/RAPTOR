using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class VitalSignTO : AbstractTO
    {
        public string id;
        public ObservationTypeTO type;
        public string value1;
        public string value2;
        public AuthorTO observer;
        public AuthorTO recorder;
        public string timestamp;
        public TaggedText facility;
        public HospitalLocationTO location;
        public string comment;
        public string units;
        public string qualifiers;

        public VitalSignTO() { }

        public VitalSignTO(VitalSign mdo)
        {
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
            this.id = mdo.Id;
            this.comment = mdo.Comment;
            this.value1 = mdo.Value1;
            this.value2 = mdo.Value2;
            this.units = mdo.Units;
            this.qualifiers = mdo.Qualifiers;
        }
    }
}
