using System;
using System.Collections.Generic;
using gov.va.medora.mdo;
using gov.va.medora.mdo.domain;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class RadiologyOrderDialogTO : AbstractTO
    {
        public TaggedTextArray contractOptions;
        public TaggedTextArray sharingOptions;
        public TaggedTextArray researchOptions;
        public TaggedTextArray categories;
        public TaggedTextArray modifiers;
        public TaggedTextArray urgencies;
        public TaggedTextArray transports;
        public TaggedTextArray submitTo;
        public List<ClinicalProcedureTO> commonProcedures;
        public List<ClinicalProcedureTO> shortList;
        public List<ImagingExamTO> lastSevenDaysExams;

        public RadiologyOrderDialogTO() { }

        public RadiologyOrderDialogTO(Exception exc)
        {
            this.fault = new FaultTO(exc);
        }

        public RadiologyOrderDialogTO(RadiologyOrderDialog mdo)
        {
            if (mdo != null)
            {
                contractOptions = new TaggedTextArray(mdo.ContractOptions);
                sharingOptions = new TaggedTextArray(mdo.SharingOptions);
                researchOptions = new TaggedTextArray(mdo.ResearchOptions);
                categories = new TaggedTextArray(mdo.Categories);
                modifiers = new TaggedTextArray(mdo.Modifiers);
                urgencies = new TaggedTextArray(mdo.Urgencies);
                transports = new TaggedTextArray(mdo.Transports);
                submitTo = new TaggedTextArray(mdo.SubmitTo);

                if (mdo.CommonProcedures != null && mdo.CommonProcedures.Count > 0)
                {
                    this.commonProcedures = new List<ClinicalProcedureTO>();
                    foreach (ClinicalProcedure proc in mdo.CommonProcedures)
                    {
                        this.commonProcedures.Add(new ClinicalProcedureTO(proc));
                    }
                }

                if (mdo.ShortList != null && mdo.ShortList.Count > 0)
                {
                    this.shortList = new List<ClinicalProcedureTO>();
                    foreach (ClinicalProcedure proc in mdo.ShortList)
                    {
                        this.shortList.Add(new ClinicalProcedureTO(proc));
                    }
                }

                if (mdo.Last7DaysExams != null && mdo.Last7DaysExams.Count > 0)
                {
                    this.lastSevenDaysExams = new List<ImagingExamTO>();
                    foreach (ImagingExam exam in mdo.Last7DaysExams)
                    {
                        this.lastSevenDaysExams.Add(new ImagingExamTO(exam));
                    }
                }
            }
        }
    }
}