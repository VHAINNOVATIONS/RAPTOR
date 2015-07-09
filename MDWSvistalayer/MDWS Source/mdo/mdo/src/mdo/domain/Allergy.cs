using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Allergy : Observation
    {
        string allergenId;
        string allergenName;
        string allergenType;
        SnoMedConcept snoMedAllergen;
        SnoMedConcept snoMedReaction;
        string severity;
        List<Symptom> reactions;
        StringDictionary drugIngredients;
        StringDictionary drugClasses;

        public string AllergenId
        {
            get { return allergenId; }
            set { allergenId = value; }
        }

        public string AllergenName
        {
            get { return allergenName; }
            set { allergenName = value; }
        }

        public string AllergenType
        {
            get { return allergenType; }
            set { allergenType = value; }
        }

        public SnoMedConcept SnoMedAllergen
        {
            get { return snoMedAllergen; }
            set { snoMedAllergen = value; }
        }

        public List<Symptom> Reactions
        {
            get { return reactions; }
            set { reactions = value; }
        }

        public StringDictionary DrugIngredients
        {
            get { return drugIngredients; }
            set { drugIngredients = value; }
        }

        public StringDictionary DrugClasses
        {
            get { return drugClasses; }
            set { drugClasses = value; }
        }

        public SnoMedConcept SnoMedReaction
        {
            get { return snoMedReaction; }
            set { snoMedReaction = value; }
        }

        public string Severity
        {
            get { return severity; }
            set { severity = value; }
        }
   
    }
}
