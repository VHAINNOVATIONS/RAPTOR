using System;
using System.Collections.Generic;

namespace gov.va.medora.mdo.domain
{
    public class RadiologyOrderDialog
    {
        public IList<ClinicalProcedure> ShortList;
        public IList<ClinicalProcedure> CommonProcedures;
        public Dictionary<String, String> Modifiers;
        public Dictionary<String, String> Urgencies;
        public Dictionary<String, String> Transports;
        public Dictionary<String, String> Categories;
        public Dictionary<String, String> SubmitTo;
        public IList<ImagingExam> Last7DaysExams;
        public Dictionary<String, String> ContractOptions;
        public Dictionary<String, String> SharingOptions;
        public Dictionary<String, String> ResearchOptions;

        public RadiologyOrderDialog() { }
    }
}
