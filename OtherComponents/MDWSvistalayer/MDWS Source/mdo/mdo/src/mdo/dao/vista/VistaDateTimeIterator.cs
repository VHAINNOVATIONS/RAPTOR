using System;
using System.Collections.Generic;
using System.Text;

using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    /// <summary>
    /// Iterator that also produces the correct mask (DdrLister.Part) for DDR LISTER queries
    /// </summary>
    /// <remarks>
    /// The key part is only providing the string of just the right length to match. Note that
    /// if your iterator is of a lesser precision than the precision of the dates then you
    /// will get some bleed at the edges of the iteration. For example, say you want to iterate
    /// by day between 12am on the start day and 3pm of the end day. The iterator will go
    /// through the whole day and the DdrLister.Part will only match on the whole day. Therefore
    /// you will get the whole end day -- the calling function will have to be responsible for
    /// cleaning up the loose ends.
    /// 
    /// An iterator of lesser precision will give greater performance (fewer iterations,
    /// appends, and queries to VistA) but it will have poorer accuracy. The application
    /// should be designed with this in mind.
    /// </remarks>
    class VistaDateTimeIterator
    {
        internal DateTime _startDate;
        internal DateTime _endDate;
        internal DateTime _iterStartDate;
        internal DateTime _iterEndDate;
        internal TimeSpan _iterLength;

        /// <summary>Allows you to manually set the iteration length. Will also set the
        /// precision.
        /// </summary>
        internal TimeSpan IterLength
        {
            get { return _iterLength; }
            set 
            { 
                // TBD VAN: Decide if I want to throw an exception if I've already started
                // iterating as you really shouldn't reset the iterator length or precision
                // during the iteration.
                _iterLength = value;
                SetPrecision();
            }
        }
        internal int _precision;
        
        // conceivable that you might want to have different minimum timespans
        // for different iterators so will leave it instance for now.
        public TimeSpan MINIMUM_ITERATION = new TimeSpan(1, 0, 0, 0);

        // constants
        public static TimeSpan DAY_ITERATION = new TimeSpan(1, 0, 0, 0);
        public static TimeSpan HOUR_ITERATION = new TimeSpan(0, 1, 0, 0);
        public static TimeSpan MINUTE_ITERATION = new TimeSpan(0, 0, 1, 0);
        public static TimeSpan SECOND_ITERATION = new TimeSpan(0, 0, 0, 1);

        public DateTime IterStartDate
        {
            get { return _iterStartDate; }
            set { _iterStartDate = value; }
        }

        public string IterStartDateString
        {
            get { return _iterStartDate.ToString("yyyyMMdd"); }
        }

        public DateTime IterEndDate
        {
            get { return _iterEndDate; }
            set { _iterEndDate = value; }
        }

        public string IterEndDateString
        {
            get { return _iterEndDate.ToString("yyyyMMdd"); }
        }

        public VistaDateTimeIterator(string startString, string endString, int timespanDays)
            : this(DateUtils.IsoDateStringToDateTime(startString)
            , DateUtils.IsoDateStringToDateTime(endString)
            , new TimeSpan(timespanDays,0,0,0)
            )
        {
        }

        // Version that automagically determines the iterator length
        public VistaDateTimeIterator(string startString, string endString)
            : this(DateUtils.IsoDateStringToDateTime(startString)
            , DateUtils.IsoDateStringToDateTime(endString)
            , new TimeSpan()
            )
        {
            SetIteratorLengthFromDateStrings(startString,endString);
            SetPrecision();
        }


        public VistaDateTimeIterator(string startString, string endString, TimeSpan iterationLength)
            : this(DateUtils.IsoDateStringToDateTime(startString)
            , DateUtils.IsoDateStringToDateTime(endString)
            , iterationLength
            )
        {
        }

        /// <summary>
        /// This is better called by converting the iteration length into a TimeSpan first
        /// using the static IterationTimeSpanFromString() first.
        /// </summary>
        /// <param name="startString"></param>
        /// <param name="endString"></param>
        /// <param name="iterationLength"></param>
        public VistaDateTimeIterator(string startString, string endString, string iterationLength)
            : this(DateUtils.IsoDateStringToDateTime(startString)
            , DateUtils.IsoDateStringToDateTime(endString)
            , IterationTimeSpanFromString(iterationLength)
            )
        {
        }


        public VistaDateTimeIterator(DateTime startDate, DateTime endDate, TimeSpan iterationLength)
        {
            this._startDate = startDate;
            this._endDate = endDate;
            _iterStartDate = this._startDate;
            this._iterLength = iterationLength;
            SetPrecision();
            // initialize the iterator
        }

        /// <summary>either the endDate, or the iteration start date + iteration length
        /// </summary>
        internal void SetIterEndDate()
        {
            if (_iterStartDate.Add(_iterLength).CompareTo(_endDate) < 0)
            {
                _iterEndDate = _iterStartDate.Add(_iterLength);
            }
            else
            {
                _iterEndDate = _endDate;
            }
        }

        internal void AdvanceIterStartDate()
        {
            _iterStartDate = _iterEndDate;
        }

        internal bool IsDone()
        {
            return (_iterStartDate.CompareTo(_endDate) >= 0);
        }

        /// <summary>
        /// Checks to see if the iteration start and end dates are equal or smaller
        /// to the shortest possible time span that this instance of the DateIterator
        /// will process.
        /// </summary>
        /// <remarks>
        ///  * Dependent on iteration length only so far as the IterEndDate is set using it.
        /// <returns></returns>
        public bool IsMinimumIteration()
        {
            return (IterEndDate.Subtract(IterStartDate) <= MINIMUM_ITERATION);
        }

        /// <summary>
        /// Looks at both the start and end dates to get the precision.
        /// </summary>
        /// <remarks> need to look at string length - 0 won't do the trick.
        /// Just need to hope that the date strings aren't normalized at some point.</remarks>
        void SetIteratorLengthFromDateStrings(string startDate, string endDate)
        {
            int length = startDate.Length > endDate.Length ? length = startDate.Length : endDate.Length;
            if (length > (int)PRECISION.MINUTE + 1)
            {
                _iterLength = SECOND_ITERATION; // 1 second iterations
            }
            else if (length == (int)PRECISION.MINUTE + 1)
            {
                _iterLength = MINUTE_ITERATION;
            }
            else if (length == (int)PRECISION.HOUR + 1)
            {
                _iterLength = HOUR_ITERATION;
            }
            else
            {
                _iterLength = DAY_ITERATION;
            }
        }

        /// <summary>Set the precision of the DdrPart based on the precision of the
        /// iteration length.
        /// </summary>
        void SetPrecision()
        {
            if (_iterLength.Milliseconds != 0 || _iterLength.Seconds != 0)
            {
                MINIMUM_ITERATION = SECOND_ITERATION;
                _precision = (int)PRECISION.SECOND;
                return;
            }
            if (_iterLength.Minutes != 0)
            {
                MINIMUM_ITERATION = MINUTE_ITERATION;
                _precision = (int)PRECISION.MINUTE;
                return;
            }
            if (_iterLength.Hours != 0)
            {
                MINIMUM_ITERATION = HOUR_ITERATION;
                _precision = (int)PRECISION.HOUR;
                return;
            }
            // otherwise just make it days
            // could extend this to larger intervals at some point in time but
            // iteration could get trickier...maybe?
            MINIMUM_ITERATION = DAY_ITERATION;
            _precision = (int)PRECISION.DAY;
            return;
        }

        /// <summary>Makes the iteration timespan from a string.
        /// </summary>
        /// <param name="iterationLength">dd.hhmmss</param>
        /// <returns></returns>
        static internal TimeSpan IterationTimeSpanFromString(string iterationLength)
        {
            int days = IterationDays(iterationLength);
            int hours = IterationHours(iterationLength);
            int minutes = IterationMinutes(iterationLength);
            int seconds = IterationSeconds(iterationLength);
            TimeSpan retVal = new TimeSpan(days, hours, minutes, seconds);
            // less than one second will default to one day
            if (retVal.CompareTo(new TimeSpan(0, 0, 1)) < 0)
            {
                return new TimeSpan(1, 0, 0, 0);
            }
            return retVal;
        }

        /// <summary>
        /// find the separator and get the value to the left of it.
        /// </summary>
        /// <param name="iterationLength"></param>
        /// <returns></returns>
        static internal int IterationDays(string iterationLength)
        {
            int retVal = 0;
            int position = iterationLength.IndexOf('.');
            if ((position > 0)
                && (int.TryParse(iterationLength.Substring(0, position ), out retVal))
                )
            {
                return retVal;
            }
            return 0;
        }

        static internal int IterationHours(string iterationLength)
        {
            int retVal = 0;
            int position = iterationLength.IndexOf('.');
            if ((iterationLength.Length > position + 2)
                &&(int.TryParse(iterationLength.Substring(position + 1,2), out retVal))
                )
            {
                return retVal;
            }
            return 0;
        }

        static internal int IterationMinutes(string iterationLength)
        {
            int retVal = 0;
            int position = iterationLength.IndexOf('.');
            if ((iterationLength.Length > position + 4)
                && (int.TryParse(iterationLength.Substring(position + 3, 2), out retVal))
                )
            {
                return retVal;
            }
            return 0;
        }

        static internal int IterationSeconds(string iterationLength)
        {
            int retVal = 0;
            int position = iterationLength.IndexOf('.');
            if ((iterationLength.Length > position + 6)
                && (int.TryParse(iterationLength.Substring(position + 5, 2), out retVal))
                )
            {
                return retVal;
            }
            return 0;
        }

        public string GetDdrListerPart()
        {
            return VistaTimestamp.fromDateTime(IterStartDate).Substring(0, _precision);
        }
        /// <summary>
        /// Also use as lengths of substrings in the VistaTimeStamp string which has format of
        /// yyyMMdd.HHmmss for DdrLister.Part()
        /// </summary>
        public enum PRECISION : int
        {
            YEAR = 3,
            MONTH = 5,
            DAY = 7,  
            DOT = 8,
            HOUR = 10,
            MINUTE = 12,
            SECOND = 14,
            MILLISECOND = 17,
        }

    }
}
