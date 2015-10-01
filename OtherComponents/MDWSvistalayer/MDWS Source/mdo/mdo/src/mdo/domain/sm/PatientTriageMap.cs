using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class PatientTriageMap : BaseModel
    {
        private TriageRelation _triageRelation;

        public TriageRelation TriageRelation
        {
            get { return _triageRelation; }
            set { _triageRelation = value; }
        }
        private Patient _patient;

        public Patient Patient
        {
            get { return _patient; }
            set { _patient = value; }
        }
    }
}
