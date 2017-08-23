using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class SymptomTO : AbstractTO
    {
        public string id;
        public string name;
        public bool isNational;
        public string vuid;
        public ObservationTypeTO type;
        public AuthorTO observer;
        public string timestamp;
        public TaggedText facility;

        public SymptomTO() { }

        public SymptomTO(Symptom mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.isNational = mdo.IsNational;
            this.vuid = mdo.Vuid;
            if (mdo.Type != null)
            {
                this.type = new ObservationTypeTO(mdo.Type);
            }
            if (mdo.Observer != null)
            {
                this.observer = new AuthorTO(mdo.Observer);
            }
            this.timestamp = mdo.Timestamp;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
        }
    }
}
