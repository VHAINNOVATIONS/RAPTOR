using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AllergyTO : AbstractTO
    {
        public string allergenId;
        public string allergenName;
        public string allergenType;
        public string reaction;
        public string severity;
        public string comment;
        public string timestamp;
        public TaggedText facility;
        public HospitalLocationTO location;
        public ObservationTypeTO type;
        public AuthorTO observer;
        public AuthorTO recorder;
        public SymptomTO[] reactions;
        public TaggedText[] drugIngredients;
        public TaggedText[] drugClasses;

        public AllergyTO() { }

        public AllergyTO(Allergy mdo)
        {
            this.allergenId = mdo.AllergenId;
            this.allergenName = mdo.AllergenName;
            this.allergenType = mdo.AllergenType;
            if (mdo.Reactions != null)
            {
                this.reactions = new SymptomTO[mdo.Reactions.Count];
                for (int i = 0; i < mdo.Reactions.Count; i++)
                {
                    this.reactions[i] = new SymptomTO(mdo.Reactions[i]);
                }
            }
            this.severity = mdo.Severity;
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
            if (mdo.DrugIngredients != null)
            {
                this.drugIngredients = new TaggedText[mdo.DrugIngredients.Count];
                int idx = 0;
                foreach (DictionaryEntry de in mdo.DrugIngredients)
                {
                    this.drugIngredients[idx++] = new TaggedText(de);
                }
            }
            if (mdo.DrugClasses != null)
            {
                this.drugClasses = new TaggedText[mdo.DrugClasses.Count];
                int idx = 0;
                foreach (DictionaryEntry de in mdo.DrugClasses)
                {
                    this.drugClasses[idx++] = new TaggedText(de);
                }
            }
        }
    }
}
