using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto
{
    public class Synopsis
    {
        public SiteArray treatingFacilities;
        public TaggedAllergyArrays allergies;
        public TaggedTextArray immunizations;
        public TaggedMedicationArrays medications;
        public TaggedMedicationArrays supplements;
        public TaggedTextArray healthSummaries;
        public TaggedTextArray detailedHealthSummaries;
        public TaggedVitalSignSetArrays vitalSigns;
        public TaggedChemHemRptArrays chemHemReports;
        public TaggedMicrobiologyRptArrays microbiologyReports;
        public TaggedProblemArrays problemLists;
        public TaggedRadiologyReportArrays radiologyReports;
        public TaggedSurgeryReportArrays surgeryReports;
        public TaggedTextArray advanceDirectives;

        public Synopsis() { }

    }
}