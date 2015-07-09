using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class MentalHealthInstrumentResultSetTO : AbstractTO
    {
        public string id;
        public string administrationId;
        public TaggedText scale;
        public string rawScore;
        public TaggedTextArray transformedScores;
        public TaggedText instrument;
        public string surveyGivenDateTime;
        public string surveySavedDateTime;

        public MentalHealthInstrumentResultSetTO() { }

        public MentalHealthInstrumentResultSetTO(MentalHealthInstrumentResultSet mdo)
        {
            this.id = mdo.Id;
            this.administrationId = mdo.AdministrationId;
            this.scale = new TaggedText(mdo.Scale);
            this.rawScore = mdo.RawScore;
            this.transformedScores = new TaggedTextArray(mdo.TransformedScores);
            this.instrument = new TaggedText(mdo.Instrument);
            this.surveyGivenDateTime = mdo.SurveyGivenDateTime;
            this.surveySavedDateTime = mdo.SurveySavedDateTime;
        }
    }
}