using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class TriageGroup : BaseModel
    {
        private string _name;

        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private string _description;

        public string Description
        {
            get { return _description; }
            set { _description = value; }
        }
        private List<Clinician> _clinicians = new List<Clinician>();

        public List<Clinician> Clinicians
        {
            get { return _clinicians; }
            set { _clinicians = value; }
        }
        private List<Patient> _patients = new List<Patient>();

        public List<Patient> Patients
        {
            get { return _patients; }
            set { _patients = value; }
        }
        private List<TriageRelation> _relations = new List<TriageRelation>();

        public List<TriageRelation> Relations
        {
            get { return _relations; }
            set { _relations = value; }
        }
        private string _vistaDiv;

        public string VistaDiv
        {
            get { return _vistaDiv; }
            set { _vistaDiv = value; }
        }
    }
}
