using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class MentalHealthInstrumentResultSet
    {
        string id;
        string administrationId;
        KeyValuePair<string, string> scale;
        string rawScore;
        StringDictionary transformedScores = new StringDictionary();
        KeyValuePair<string, string> instrument;

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string AdministrationId
        {
            get { return administrationId; }
            set { administrationId = value; }
        }

        public KeyValuePair<string, string> Scale
        {
            get { return scale; }
            set { scale = value; }
        }

        public string RawScore
        {
            get { return rawScore; }
            set { rawScore = value; }
        }

        public StringDictionary TransformedScores
        {
            get { return transformedScores; }
            set { transformedScores = value; }
        }

        public KeyValuePair<string, string> Instrument
        {
            get { return instrument; }
            set { instrument = value; }
        }

        public string SurveyGivenDateTime { get; set; }

        public string SurveySavedDateTime { get; set; }
    }
}
