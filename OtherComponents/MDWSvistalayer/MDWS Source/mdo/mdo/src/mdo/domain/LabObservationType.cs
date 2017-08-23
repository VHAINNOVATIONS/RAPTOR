using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabObservationType
    {
        String id;
        String title;
        String units;
        String range;

        public LabObservationType(String id, String title, String units, String range)
        {
            Id = id;
            Title = title;
            Units = units;
            Range = range;
        }

        public LabObservationType(String id, String title)
        {
            Id = id;
            Title = title;
        }

        public LabObservationType() { }

        public String Id
        {
            get
            {
                return id;
            }
            set
            {
                id = value;
            }
        }

        public String Title
        {
            get
            {
                return title;
            }
            set
            {
                title = value;
            }
        }

        public String Units
        {
            get
            {
                return units;
            }
            set
            {
                units = value;
            }
        }

        public String Range
        {
            get
            {
                return range;
            }
            set
            {
                range = value;
            }
        }

    }
}
