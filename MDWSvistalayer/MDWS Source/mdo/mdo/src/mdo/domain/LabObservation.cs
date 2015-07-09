using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabObservation
    {
        LabObservationType type;
        String value;
        String value1;
        String value2;
        String method;
        String qualifier;
        String standardized;
        String device;
        String status;
        DateTime timestamp;

        public LabObservation() {}

        public LabObservationType Type
        {
            get
            {
                return type;
            }
            set
            {
                type = value;
            }
        }

        public String Value
        {
            get
            {
                return value;
            }
            set
            {
                this.value = value;
            }
        }

        public String Value1
        {
            get
            {
                return value1;
            }
            set
            {
                value1 = value;
            }
        }

        public String Value2
        {
            get
            {
                return value2;
            }
            set
            {
                value2 = value;
            }
        }

        public String Method
        {
            get
            {
                return method;
            }
            set
            {
                method = value;
            }
        }

        public String Qualifier
        {
            get
            {
                return qualifier;
            }
            set
            {
                qualifier = value;
            }
        }

        public String Standardized
        {
            get
            {
                return standardized;
            }
            set
            {
                standardized = value;
            }
        }

        public String Device
        {
            get
            {
                return device;
            }
            set
            {
                device = value;
            }
        }

        public String Status
        {
            get
            {
                return status;
            }
            set
            {
                status = value;
            }
        }

        public DateTime Timestamp
        {
            get
            {
                return timestamp;
            }
            set
            {
                timestamp = value;
            }
        }
    }
}
